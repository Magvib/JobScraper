<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_cv_page_shows_template_gallery(): void
    {
        $user = User::factory()->create([
            'skills' => ['PHP', 'Laravel'],
        ]);

        $response = $this->actingAs($user)->get('/cv');

        $response->assertOk();
        $response->assertSee('CV Templates');
        $response->assertSee('Classic Serif');
        $response->assertSee('Comic Book Pop');
        $response->assertSee('/template/temp1');
        $response->assertSee('/template/temp41');
    }

    public function test_template_route_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'skills' => ['PHP', 'Laravel'],
        ]);

        $response = $this->actingAs($user)->get('/template/temp2');

        $response->assertOk();
        $response->assertSee($user->name);
    }
}