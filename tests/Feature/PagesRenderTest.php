<?php

namespace Tests\Feature;

use App\Models\JobRating;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'keywords' => ['php', 'laravel'],
            'skills' => ['PHP', 'Laravel'],
            'latitude' => 56.45,
            'longitude' => 9.4,
            'city' => 'Viborg',
        ], $attributes));
    }

    /**
     * Livewire's test harness cannot drive islands, so replay what the browser sends: a real
     * update request for the `jobs` island (lazy load, or a method call scoped to the island).
     * Returns the rendered island HTML.
     */
    private function renderIsland(array $updates = [], string $method = '__lazyLoadIsland', array $params = [], ?string $snapshot = null): string
    {
        $snapshot ??= json_encode(Livewire::test('jobs')->snapshot);

        $response = $this->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => $updates,
                'calls' => [[
                    'method' => $method,
                    'params' => $params,
                    'metadata' => ['island' => ['name' => 'jobs', 'mode' => 'morph']],
                ]],
            ]],
        ], ['X-Livewire' => '1']);

        $response->assertOk();

        return json_encode($response->json('components.0.effects.islandFragments') ?? $response->json());
    }

    private function seedPosts(int $count = 3): void
    {
        foreach (range(1, $count) as $i) {
            Post::create([
                'source' => $i % 2 ? 'jobindex' : 'jobnet',
                'source_id' => "id-$i",
                'keyword' => $i % 2 ? 'php' : 'laravel',
                'title' => "Developer $i",
                'company_name' => "Company $i",
                'city' => $i === 1 ? 'Viborg' : 'Aarhus',
                'canonical_url' => "https://example.com/job/$i",
                'published_at' => now()->subHours($i),
                'description' => $i === 1 ? '<p onclick="x()">Hello</p><script>alert(1)</script>' : null,
            ]);
        }
    }

    public function test_every_page_renders_with_the_new_layout(): void
    {
        $user = $this->user();

        foreach (['/dashboard', '/profile', '/cv', '/cover-letter', '/templates', '/jobs'] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk()->assertSee('Scraper');
        }
    }

    public function test_welcome_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Sign in with GitHub');
    }

    public function test_jobs_page_defers_the_heavy_work_into_a_lazy_island(): void
    {
        $user = $this->user();
        $this->seedPosts();

        // The initial page request must not scrape or list jobs.
        Http::fake();

        $this->actingAs($user)->get('/jobs')
            ->assertOk()
            ->assertSee('Fetching the latest listings')
            ->assertDontSee('Developer 1');

        Http::assertNothingSent();
    }

    private function warmScrapeCache(User $user): void
    {
        Cache::put('jobindex_'.md5(json_encode($user->keywords)), 1, 600);
        Cache::put('jobnet_'.md5(json_encode($user->keywords)), 1, 600);
    }

    public function test_jobs_island_lists_jobs_with_ratings_and_applies_filters(): void
    {
        $user = $this->user();
        $this->seedPosts();
        $this->warmScrapeCache($user);

        JobRating::create([
            'user_id' => $user->id, 'source' => 'jobindex', 'job_id' => 'id-1',
            'job_title' => 'Developer 1', 'status' => 'completed', 'skills_match' => 91,
            'experience_relevance' => 70, 'seniority_fit' => 40, 'keyword_match' => 85,
        ]);

        $this->actingAs($user);
        $snapshot = json_encode(Livewire::test('jobs')->snapshot);

        $all = $this->renderIsland(snapshot: $snapshot);
        $this->assertStringContainsString('Developer 1', $all);
        $this->assertStringContainsString('Developer 2', $all);
        $this->assertStringContainsString('91%', $all);
        $this->assertStringContainsString('3 jobs found', $all);

        // Search narrows the list.
        $search = $this->renderIsland(['search' => 'Developer 2'], '$refresh', snapshot: $snapshot);
        $this->assertStringContainsString('Developer 2', $search);
        $this->assertStringNotContainsString('Developer 1', $search);

        // Keyword filter.
        $keyword = $this->renderIsland([], 'toggleKeyword', ['laravel'], $snapshot);
        $this->assertStringContainsString('Developer 2', $keyword);
        $this->assertStringNotContainsString('Developer 1', $keyword);

        // Distance filter: Viborg (the user's city) is in range, Aarhus (~70 km) is not at 20 km.
        $near = $this->renderIsland(['filterByDistance' => true, 'maxDistance' => 20], '$refresh', snapshot: $snapshot);
        $this->assertStringContainsString('Developer 1', $near);
        $this->assertStringNotContainsString('Developer 2', $near);
    }

    public function test_jobs_island_paginates_and_lists_a_single_poll_for_pending_scores(): void
    {
        $user = $this->user();
        $this->seedPosts(30);
        $this->warmScrapeCache($user);

        foreach ([1, 2, 3] as $i) {
            JobRating::create([
                'user_id' => $user->id, 'source' => 'jobindex', 'job_id' => "id-$i",
                'job_title' => "Developer $i", 'status' => 'pending',
            ]);
        }

        $this->actingAs($user);
        $snapshot = json_encode(Livewire::test('jobs')->snapshot);

        $html = $this->renderIsland(snapshot: $snapshot);

        $this->assertSame(24, substr_count($html, 'View Job'), 'Only one page of cards is rendered');
        $this->assertStringContainsString('Show more', $html);
        $this->assertSame(1, substr_count($html, 'wire:poll'), 'One poll for the whole island, not one per card');

        $more = $this->renderIsland([], 'loadMore', [], $snapshot);
        $this->assertSame(30, substr_count($more, 'View Job'));
        $this->assertStringNotContainsString('Show more', $more);
    }

    public function test_description_modal_sanitises_scraped_html(): void
    {
        $user = $this->user();
        $this->seedPosts();
        $this->warmScrapeCache($user);
        $this->actingAs($user);

        $id = Post::where('source_id', 'id-1')->value('id');
        $html = $this->renderIsland([], 'viewDescription', [$id]);

        $this->assertStringContainsString('Hello', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_jobs_snapshot_stays_small_and_does_not_carry_the_city_table(): void
    {
        $user = $this->user();
        $this->seedPosts();
        $this->warmScrapeCache($user);
        $this->actingAs($user);

        $snapshot = json_encode(Livewire::test('jobs')->snapshot);

        $this->assertStringNotContainsString('Hørsholm', $snapshot);
        $this->assertLessThan(3000, strlen($snapshot));
    }

    public function test_jobs_island_query_count_does_not_grow_with_the_number_of_jobs(): void
    {
        $user = $this->user();
        $this->warmScrapeCache($user);
        $this->actingAs($user);

        $count = function (int $jobs): int {
            Post::query()->delete();
            $this->seedPosts($jobs);
            $snapshot = json_encode(Livewire::test('jobs')->snapshot);

            \DB::flushQueryLog();
            \DB::enableQueryLog();
            $this->renderIsland(snapshot: $snapshot);

            return count(\DB::getQueryLog());
        };

        $small = $count(3);
        $large = $count(24);

        $this->assertSame($small, $large, "Queries grew with job count: $small vs $large");
    }

    public function test_profile_renders_and_saves_with_keywords_links_and_every_question_type(): void
    {
        $user = $this->user([
            'questions' => [
                ['key' => 'remote_ok', 'type' => 'boolean', 'question' => 'Remote?'],
                ['key' => 'work_mode', 'type' => 'choice', 'question' => 'Mode?', 'options' => ['hybrid' => 'Some days', 'onsite' => null]],
                ['key' => 'seniority', 'type' => 'score', 'question' => 'Level?', 'levels' => ['Junior', 'Mid', 'Senior']],
            ],
        ]);
        $user->links()->create(['name' => 'GitHub', 'url' => 'https://github.com/example']);

        $this->actingAs($user);

        $this->get('/profile')->assertOk()
            ->assertSee('Search Keywords')->assertSee('remote_ok')->assertSee('Some days')->assertSee('Junior')->assertSee('GitHub');

        Livewire::test('profile')
            ->set('newKeyword', 'symfony')->call('addKeyword')
            ->set('jobTitle', 'Engineer')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('symfony');

        $user->refresh();
        $this->assertContains('symfony', $user->keywords);
        $this->assertSame('Engineer', $user->job_title);
        $this->assertCount(3, $user->questions);
        $this->assertCount(1, $user->links);
    }

    public function test_dashboard_shows_stats_and_activity(): void
    {
        $user = $this->user();

        JobRating::create([
            'user_id' => $user->id, 'source' => 'jobindex', 'job_id' => 'a', 'job_title' => 'Great job',
            'status' => 'completed', 'skills_match' => 90, 'experience_relevance' => 80, 'seniority_fit' => 85, 'keyword_match' => 95,
        ]);
        JobRating::create(['user_id' => $user->id, 'source' => 'jobindex', 'job_id' => 'b', 'job_title' => 'Pending job', 'status' => 'pending']);

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Great job')->assertSee('Pending job')->assertSee('Strong Matches')->assertSee('1 still calculating');
    }

    public function test_templates_page_opens_the_preview_dialog_with_the_users_cover_letters(): void
    {
        $user = $this->user();
        $user->coverLetters()->create(['title' => 'My Letter', 'content' => 'Hello']);

        $this->actingAs($user);

        Livewire::test('templates')
            ->call('select', 'temp1')
            ->assertSee('role="dialog"', false)
            ->assertSee('My Letter')
            ->assertSee('Download CV')
            ->call('close')
            ->assertDontSee('role="dialog"', false);
    }

    public function test_cover_letter_page_saves_and_lists_letters(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        Livewire::test('cover_letter')
            ->set('title', 'Dream job')
            ->set('content', 'Dear hiring manager')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Dream job');

        $this->assertDatabaseHas('cover_letters', ['user_id' => $user->id, 'title' => 'Dream job']);
    }

    public function test_cv_page_saves_entries(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        Livewire::test('cv')
            ->set('jobs', [['title' => 'Dev', 'company' => 'Acme', 'startDate' => '2020-01-01', 'endDate' => null, 'description' => 'x']])
            ->call('save');

        $this->assertSame('Dev', $user->fresh()->cv_json[0]['title']);
    }
}
