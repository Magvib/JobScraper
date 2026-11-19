<?php

use App\Ai\Agents\KeywordSpecialist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Document;
use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public string $address = '';

    public string $zip = '';

    public string $city = '';

    public int $maxDistance = 50;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public string $email = '';

    public string $phone = '';

    public ?string $birthdate = null;

    public string $jobTitle = '';

    public $image = null;

    public array $keywords = [];

    public string $newKeyword = '';

    public array $skills = [];

    public string $newSkill = '';

    public array $links = [];

    public string $newLinkName = '';

    public string $newLinkUrl = '';

    public array $questions = [];

    public string $newQuestionKey = '';

    public string $newQuestionType = 'boolean';

    public string $newQuestionText = '';

    public array $newQuestionOptions = [
        ['name' => '', 'description' => ''],
        ['name' => '', 'description' => ''],
    ];

    public array $newQuestionLevels = ['', ''];

    public bool $autoMatchNewJobs = false;

    public ?float $notifySkillsMatchThreshold = null;

    public ?float $notifyExperienceRelevanceThreshold = null;

    public ?float $notifySeniorityFitThreshold = null;

    public ?float $notifyKeywordMatchThreshold = null;

    public string $notifyMatchMode = 'any';

    public ?int $defaultCoverLetter = null;

    public array $coverLetters = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->address = $user->address ?? '';
        $this->zip = $user->zip ?? '';
        $this->city = $user->city ?? '';
        $this->latitude = $user->latitude;
        $this->longitude = $user->longitude;
        $this->maxDistance = $user->max_distance ?? 50;
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->birthdate = $user->birthdate?->format('Y-m-d');
        $this->jobTitle = $user->job_title ?? '';
        $this->keywords = $user->keywords ?? [];
        $this->skills = $user->skills ?? [];
        $this->links = $user->links->map(fn ($link) => ['name' => $link->name, 'url' => $link->url])->all();
        $this->questions = collect($user->questions ?? [])->map(function (array $question) {
            if (isset($question['options'])) {
                $question['options'] = collect($question['options'])
                    ->map(fn ($description, $name) => ['name' => $name, 'description' => $description ?? ''])
                    ->values()
                    ->all();
            }

            if (isset($question['levels'])) {
                $question['levels'] = array_values($question['levels']);
            }

            return $question;
        })->all();
        $this->autoMatchNewJobs = (bool) $user->auto_match_new_jobs;
        $this->notifySkillsMatchThreshold = $user->notify_skills_match_threshold;
        $this->notifyExperienceRelevanceThreshold = $user->notify_experience_relevance_threshold;
        $this->notifySeniorityFitThreshold = $user->notify_seniority_fit_threshold;
        $this->notifyKeywordMatchThreshold = $user->notify_keyword_match_threshold;
        $this->notifyMatchMode = $user->notify_match_mode ?? 'any';
        $this->defaultCoverLetter = $user->default_cover_letter_id;
        $this->coverLetters = $user->coverLetters()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title'])
            ->map(fn ($letter) => ['id' => $letter->id, 'title' => $letter->title ?? __('Untitled')])
            ->all();
    }

    public function save(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'min:2', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
            'maxDistance' => ['required', 'integer', 'min:1', 'max:500'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'phone' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'jobTitle' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:5120'],
            'autoMatchNewJobs' => ['boolean'],
            'notifySkillsMatchThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyExperienceRelevanceThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifySeniorityFitThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyKeywordMatchThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyMatchMode' => ['required', 'in:any,all'],
            'defaultCoverLetter' => ['nullable', 'integer', 'in:' . implode(',', array_column($this->coverLetters, 'id'))],
            'links' => ['array'],
            'links.*.name' => ['required', 'string', 'max:255'],
            'links.*.url' => ['required', 'url', 'max:255'],
            'questions' => ['array'],
            'questions.*.key' => ['required', 'string', 'regex:/^[a-z0-9_]{2,64}$/', 'distinct'],
            'questions.*.type' => ['required', 'in:boolean,choice,score'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.name' => ['required', 'string', 'max:64'],
            'questions.*.options.*.description' => ['nullable', 'string', 'max:255'],
            'questions.*.levels' => ['nullable', 'array'],
            'questions.*.levels.*' => ['required', 'string', 'max:64'],
        ]);

        $user = auth()->user();
        $user->name = $this->username;
        $user->email = $this->email;
        $user->address = $this->address ?: null;
        $user->zip = $this->zip ?: null;
        $user->city = $this->city ?: null;

        // Look up the user's coordinates from their address, zip and city.
        if (($this->address || $this->zip || $this->city) && $this->latitude === null) {
            [$this->latitude, $this->longitude] = $this->geocodeLocation();
    
            if ($this->latitude === null) {
                $this->dispatch(
                    'toast',
                    message: __('Could not find coordinates for your address. Please check your zip and city.'),
                    type: 'error'
                );
            }
        }

        $user->latitude = $this->latitude;
        $user->longitude = $this->longitude;
        $user->phone = $this->phone ?: null;
        $user->birthdate = $this->birthdate ?: null;
        $user->job_title = $this->jobTitle ?: null;

        if ($this->image) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $path = $this->image->store('avatars', 'public');
            $user->image = $path;
        }
        $user->max_distance = $this->maxDistance;
        $user->auto_match_new_jobs = $this->autoMatchNewJobs;
        $user->notify_skills_match_threshold = $this->notifySkillsMatchThreshold;
        $user->notify_experience_relevance_threshold = $this->notifyExperienceRelevanceThreshold;
        $user->notify_seniority_fit_threshold = $this->notifySeniorityFitThreshold;
        $user->notify_keyword_match_threshold = $this->notifyKeywordMatchThreshold;
        $user->notify_match_mode = $this->notifyMatchMode;
        $user->default_cover_letter_id = $this->defaultCoverLetter ?: null;

        $user->keywords = $this->keywords;
        $user->skills = $this->skills;

        // Convert the editable question definitions into the stored JSON shape.
        $questions = [];

        foreach ($this->questions as $question) {
            $definition = [
                'key' => $question['key'],
                'type' => $question['type'],
                'question' => $question['question'],
            ];

            if ($question['type'] === 'choice') {
                $options = [];

                foreach ($question['options'] ?? [] as $option) {
                    $name = Str::slug(trim($option['name'] ?? ''), '_');

                    if ($name === '') {
                        continue;
                    }

                    $options[$name] = trim($option['description'] ?? '') ?: null;
                }

                if (count($options) < 2) {
                    $this->dispatch(
                        'toast',
                        message: __('The question ":key" needs at least two options.', ['key' => $question['key']]),
                        type: 'error'
                    );

                    return;
                }

                $definition['options'] = $options;
            } elseif ($question['type'] === 'score') {
                $levels = collect($question['levels'] ?? [])
                    ->map(fn ($level) => trim($level))
                    ->filter()
                    ->values()
                    ->all();

                if (count($levels) < 2) {
                    $this->dispatch(
                        'toast',
                        message: __('The question ":key" needs at least two levels.', ['key' => $question['key']]),
                        type: 'error'
                    );

                    return;
                }

                $definition['levels'] = $levels;
            }

            $questions[] = $definition;
        }

        $user->questions = $questions;
        $user->save();

        $user->links()->delete();
        $user->links()->createMany($this->links);
        $this->dispatch(
            'toast',
            message: __('Profile saved successfully.'),
            type: 'success'
        );
    }

    /**
     * Look up coordinates for the user's address, zip and city
     * using the OpenStreetMap Nominatim API.
     *
     * @return array{0: float, 1: float}|null [latitude, longitude] or null when no match was found.
     */
    public function geocodeLocation(): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => config('app.name') . ' (' . config('app.url') . ')',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'limit' => 1,
                'street' => $this->address ?: null,
                'postalcode' => $this->zip ?: null,
                'city' => $this->city ?: null,
                'countrycodes' => 'dk',
            ]);

            $result = $response->json('0');

            if ($response->failed() || ! isset($result['lat'], $result['lon'])) {
                return null;
            }

            return [(float) $result['lat'], (float) $result['lon']];
        } catch (\Throwable) {
            return null;
        }
    }

    public function forceUpdateCords()
    {
        [$this->latitude, $this->longitude] = $this->geocodeLocation() ?? [null, null];

        if (($this->address || $this->zip || $this->city) && $this->latitude === null) {
            $this->dispatch(
                'toast',
                message: __('Could not find coordinates for your address. Please check your zip and city.'),
                type: 'error'
            );
        }

        $this->save();
    }

    public function addKeyword(): void
    {
        $keyword = trim($this->newKeyword);
        if ($keyword && ! in_array($keyword, $this->keywords)) {
            $this->keywords[] = $keyword;
            $this->newKeyword = '';
        }
    }

    public function removeKeyword(int $index): void
    {
        if (isset($this->keywords[$index])) {
            unset($this->keywords[$index]);
            $this->keywords = array_values($this->keywords);
        }
    }

    public function addSkill(): void
    {
        $skill = trim($this->newSkill);
        if ($skill && ! in_array($skill, $this->skills)) {
            $this->skills[] = $skill;
            $this->newSkill = '';
        }
    }

    public function removeSkill(int $index): void
    {
        if (isset($this->skills[$index])) {
            unset($this->skills[$index]);
            $this->skills = array_values($this->skills);
        }
    }

    public function addLink(): void
    {
        $name = trim($this->newLinkName);
        $url = trim($this->newLinkUrl);

        // Assume https:// when the user left out the scheme.
        if ($url && ! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        if ($name && $url && ! collect($this->links)->contains(fn ($link) => $link['name'] === $name && $link['url'] === $url)) {
            $this->links[] = ['name' => $name, 'url' => $url];
            $this->newLinkName = '';
            $this->newLinkUrl = '';
        }
    }

    public function removeLink(int $index): void
    {
        if (isset($this->links[$index])) {
            unset($this->links[$index]);
            $this->links = array_values($this->links);
        }
    }

    public function addQuestion(): void
    {
        $key = Str::slug(trim($this->newQuestionKey), '_');
        $question = trim($this->newQuestionText);

        if ($key === '' || $question === '') {
            $this->dispatch(
                'toast',
                message: __('A key and a question are required.'),
                type: 'error'
            );

            return;
        }

        if (collect($this->questions)->contains(fn ($q) => $q['key'] === $key)) {
            $this->dispatch(
                'toast',
                message: __('A question with this key already exists.'),
                type: 'error'
            );

            return;
        }

        $definition = [
            'key' => $key,
            'type' => $this->newQuestionType,
            'question' => $question,
        ];

        if ($this->newQuestionType === 'choice') {
            $options = collect($this->newQuestionOptions)
                ->filter(fn ($option) => trim($option['name'] ?? '') !== '')
                ->map(fn ($option) => ['name' => trim($option['name']), 'description' => trim($option['description'] ?? '')])
                ->values()
                ->all();

            if (count($options) < 2) {
                $this->dispatch(
                    'toast',
                    message: __('A choice question needs at least two options with a name.'),
                    type: 'error'
                );

                return;
            }

            $definition['options'] = $options;
        } elseif ($this->newQuestionType === 'score') {
            $levels = collect($this->newQuestionLevels)
                ->map(fn ($level) => trim($level))
                ->filter()
                ->values()
                ->all();

            if (count($levels) < 2) {
                $this->dispatch(
                    'toast',
                    message: __('A score question needs at least two levels.'),
                    type: 'error'
                );

                return;
            }

            $definition['levels'] = $levels;
        }

        $this->questions[] = $definition;

        $this->newQuestionKey = '';
        $this->newQuestionText = '';
        $this->newQuestionOptions = [
            ['name' => '', 'description' => ''],
            ['name' => '', 'description' => ''],
        ];
        $this->newQuestionLevels = ['', ''];
    }

    public function removeQuestion(int $index): void
    {
        if (isset($this->questions[$index])) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
    }

    public function addQuestionOption(int $index): void
    {
        $this->questions[$index]['options'][] = ['name' => '', 'description' => ''];
    }

    public function removeQuestionOption(int $questionIndex, int $optionIndex): void
    {
        if (isset($this->questions[$questionIndex]['options'][$optionIndex])) {
            unset($this->questions[$questionIndex]['options'][$optionIndex]);
            $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
        }
    }

    public function addQuestionLevel(int $index): void
    {
        $this->questions[$index]['levels'][] = '';
    }

    public function removeQuestionLevel(int $questionIndex, int $levelIndex): void
    {
        if (isset($this->questions[$questionIndex]['levels'][$levelIndex])) {
            unset($this->questions[$questionIndex]['levels'][$levelIndex]);
            $this->questions[$questionIndex]['levels'] = array_values($this->questions[$questionIndex]['levels']);
        }
    }

    public function addNewQuestionOption(): void
    {
        $this->newQuestionOptions[] = ['name' => '', 'description' => ''];
    }

    public function removeNewQuestionOption(int $index): void
    {
        unset($this->newQuestionOptions[$index]);
        $this->newQuestionOptions = array_values($this->newQuestionOptions);
    }

    public function addNewQuestionLevel(): void
    {
        $this->newQuestionLevels[] = '';
    }

    public function removeNewQuestionLevel(int $index): void
    {
        unset($this->newQuestionLevels[$index]);
        $this->newQuestionLevels = array_values($this->newQuestionLevels);
    }

    public function generateKeywords(): void
    {
        $cvJson = json_encode(auth()->user()->cv_json);

        if (! $cvJson) {
            return;
        }
        
        $keywords = (new KeywordSpecialist)->prompt(
            <<<PROMPT
            Give me the keywords for this CV.
            
            CV: $cvJson
            PROMPT
        );
        $this->keywords = $keywords->structured['keywords'] ?? [];

        $this->dispatch(
            'toast',
            message: __('Keywords are being generated. Please check back in a few moments.'),
            type: 'success'
        );
    }
}
?>

<div>
    <form wire:submit="save" class="pb-24">
        <x-page-header :title="__('Profile Settings')" :description="__('Your details, search keywords and AI preferences.')">
            <x-ui.avatar :src="auth()->user()->getImage()" :name="auth()->user()->name" size="size-10" />
        </x-page-header>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
            {{-- Left column: who you are --}}
            <div class="space-y-6">
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Account') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('This is how you appear to others') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-4">
                        <x-ui.field :label="__('Username')" for="username" error="username">
                            <x-ui.input id="username" wire:model="username" placeholder="{{ __('Enter your username') }}" required minlength="2" maxlength="50" />
                        </x-ui.field>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Information') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Shown on your CV and cover letters.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-4">
                        <x-ui.field :label="__('Profile Image')" for="image" :hint="__('JPG or PNG up to 5MB')" error="image">
                            <div class="flex items-center gap-4">
                                @if ($image)
                                    <x-ui.avatar :src="$image->temporaryUrl()" :name="$username" size="size-16" />
                                @else
                                    <x-ui.avatar :src="auth()->user()->getImage()" :name="$username" size="size-16" />
                                @endif
                                <x-ui.input id="image" type="file" wire:model="image" accept="image/*" class="h-auto py-1.5" />
                            </div>
                            <div wire:loading wire:target="image" class="flex items-center gap-2 text-xs text-muted-foreground">
                                <x-ui.spinner /> {{ __('Uploading…') }}
                            </div>
                        </x-ui.field>

                        <x-ui.field :label="__('Email')" for="email" error="email">
                            <x-ui.input id="email" type="email" wire:model="email" placeholder="{{ __('name@p13.dk') }}" required maxlength="255" />
                        </x-ui.field>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.field :label="__('Phone Number')" for="phone" error="phone">
                                <x-ui.input id="phone" type="tel" wire:model="phone" placeholder="{{ __('+45 12 34 56 78') }}" maxlength="20" />
                            </x-ui.field>
                            <x-ui.field :label="__('Birthdate')" for="birthdate" error="birthdate">
                                <x-ui.input id="birthdate" type="date" wire:model="birthdate" max="{{ now()->format('Y-m-d') }}" />
                            </x-ui.field>
                        </div>

                        <x-ui.field :label="__('Job Title')" for="jobTitle" error="jobTitle">
                            <x-ui.input id="jobTitle" wire:model="jobTitle" placeholder="{{ __('Software Developer') }}" maxlength="100" />
                        </x-ui.field>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Location') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Used to measure how far away each job is.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-4">
                        <x-ui.field :label="__('Street Address')" for="address" error="address">
                            <x-ui.input id="address" wire:model="address" placeholder="{{ __('123 Main St') }}" maxlength="255" />
                        </x-ui.field>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <x-ui.field :label="__('Zip / Postal Code')" for="zip" error="zip">
                                <x-ui.input id="zip" wire:model="zip" placeholder="{{ __('8800') }}" maxlength="10" />
                            </x-ui.field>
                            <x-ui.field :label="__('City')" for="city" class="sm:col-span-2" error="city">
                                <x-ui.input id="city" wire:model="city" placeholder="{{ __('Viborg') }}" maxlength="100" />
                            </x-ui.field>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.field :label="__('Latitude')" for="latitude">
                                <x-ui.input id="latitude" wire:model="latitude" readonly placeholder="{{ __('Set automatically on save') }}" />
                            </x-ui.field>
                            <x-ui.field :label="__('Longitude')" for="longitude">
                                <x-ui.input id="longitude" wire:model="longitude" readonly placeholder="{{ __('Set automatically on save') }}" />
                            </x-ui.field>
                        </div>

                        <div class="flex flex-wrap items-end gap-4">
                            <x-ui.field :label="__('Max Distance (km)')" for="maxDistance" error="maxDistance" class="w-40">
                                <x-ui.input id="maxDistance" type="number" wire:model="maxDistance" min="1" max="500" />
                            </x-ui.field>
                            <x-ui.button variant="secondary" wire:click="forceUpdateCords" wire:loading.attr="disabled" wire:target="forceUpdateCords">
                                <x-ui.spinner wire:loading wire:target="forceUpdateCords" />
                                <x-ui.icon name="map-pin" wire:loading.remove wire:target="forceUpdateCords" />
                                {{ __('Update Coordinates') }}
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            {{-- Right column: what you are looking for --}}
            <div class="space-y-6">
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Search Keywords') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('These keywords are used to find matching jobs based on your CV. You can add or remove keywords.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-3">
                        @if ($keywords)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($keywords as $index => $keyword)
                                    <x-ui.badge variant="secondary" class="gap-1.5 py-1 pl-2.5 pr-1.5 text-sm" wire:key="keyword-{{ $index }}-{{ $keyword }}">
                                        {{ $keyword }}
                                        <button type="button" wire:click="removeKeyword({{ $index }})" class="rounded-sm p-0.5 text-muted-foreground transition-colors hover:text-destructive" aria-label="{{ __('Remove') }}">
                                            <x-ui.icon name="x" size="size-3.5" />
                                        </button>
                                    </x-ui.badge>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <x-ui.input wire:model="newKeyword" wire:keydown.enter.prevent="addKeyword" placeholder="{{ __('Add a keyword') }}" class="min-w-40 flex-1" />
                            <x-ui.button variant="secondary" wire:click="addKeyword">{{ __('Add') }}</x-ui.button>
                            <x-ui.button variant="outline" wire:click="generateKeywords" wire:loading.attr="disabled" wire:target="generateKeywords">
                                <x-ui.spinner wire:loading wire:target="generateKeywords" />
                                <x-ui.icon name="sparkles" wire:loading.remove wire:target="generateKeywords" />
                                {{ __('Generate from CV') }}
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Skills') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Add the skills you have. These are displayed on your CV.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-3">
                        @if ($skills)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($skills as $index => $skill)
                                    <x-ui.badge variant="outline" class="gap-1.5 py-1 pl-2.5 pr-1.5 text-sm" wire:key="skill-{{ $index }}-{{ $skill }}">
                                        {{ $skill }}
                                        <button type="button" wire:click="removeSkill({{ $index }})" class="rounded-sm p-0.5 text-muted-foreground transition-colors hover:text-destructive" aria-label="{{ __('Remove') }}">
                                            <x-ui.icon name="x" size="size-3.5" />
                                        </button>
                                    </x-ui.badge>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <x-ui.input wire:model="newSkill" wire:keydown.enter.prevent="addSkill" placeholder="{{ __('Add a skill') }}" class="min-w-40 flex-1" />
                            <x-ui.button variant="secondary" wire:click="addSkill">{{ __('Add') }}</x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Links') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Add links to your GitHub, LinkedIn, portfolio and more. These are displayed on your CV.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-3">
                        @foreach ($links as $index => $link)
                            <div class="flex items-center justify-between gap-2 rounded-lg border px-3 py-2" wire:key="link-{{ $index }}-{{ $link['url'] }}">
                                <div class="min-w-0">
                                    <span class="text-sm font-medium">{{ $link['name'] }}</span>
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="block truncate text-sm text-muted-foreground hover:text-foreground hover:underline">
                                        {{ $link['url'] }}
                                    </a>
                                </div>
                                <x-ui.button variant="ghost" size="icon-sm" wire:click="removeLink({{ $index }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                    <x-ui.icon name="x" />
                                </x-ui.button>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <x-ui.input wire:model="newLinkName" placeholder="{{ __('Name (e.g. GitHub)') }}" maxlength="255" />
                            <x-ui.input wire:model="newLinkUrl" wire:keydown.enter.prevent="addLink" placeholder="{{ __('URL (e.g. github.com/username)') }}" maxlength="255" />
                        </div>
                        @if ($errors->has('links.*'))
                            <p class="text-xs font-medium text-destructive">{{ collect($errors->get('links.*'))->flatten()->first() }}</p>
                        @endif
                        <x-ui.button variant="secondary" wire:click="addLink">{{ __('Add') }}</x-ui.button>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('AI Questions') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Define your own questions that the AI answers about each job posting. Answer them from a job with the Answer Questions button.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-3">
                        @foreach ($questions as $index => $question)
                            <div class="space-y-2 rounded-lg border p-3" wire:key="question-{{ $index }}-{{ $question['key'] }}">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge variant="outline" class="font-mono">{{ $question['key'] }}</x-ui.badge>
                                        <x-ui.badge variant="muted">{{ __(Str::title($question['type'])) }}</x-ui.badge>
                                    </div>
                                    <x-ui.button variant="ghost" size="icon-sm" wire:click="removeQuestion({{ $index }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                        <x-ui.icon name="trash" />
                                    </x-ui.button>
                                </div>

                                <x-ui.field error="questions.{{ $index }}.question">
                                    <x-ui.input wire:model="questions.{{ $index }}.question" maxlength="1000" placeholder="{{ __('Question') }}" />
                                </x-ui.field>

                                @if ($question['type'] === 'choice')
                                    <div class="space-y-2">
                                        <p class="text-xs text-muted-foreground">{{ __('Options (at least two)') }}</p>
                                        @foreach ($question['options'] ?? [] as $optionIndex => $option)
                                            <div wire:key="question-{{ $index }}-option-{{ $optionIndex }}">
                                                <div class="flex items-center gap-2">
                                                    <x-ui.input
                                                        wire:model="questions.{{ $index }}.options.{{ $optionIndex }}.name"
                                                        class="w-1/3"
                                                        aria-invalid="{{ $errors->has('questions.'.$index.'.options.'.$optionIndex.'.name') ? 'true' : 'false' }}"
                                                        placeholder="{{ __('Name (e.g. hybrid)') }}"
                                                        maxlength="64"
                                                    />
                                                    <x-ui.input wire:model="questions.{{ $index }}.options.{{ $optionIndex }}.description" placeholder="{{ __('Optional description') }}" maxlength="255" />
                                                    <x-ui.button variant="ghost" size="icon-sm" wire:click="removeQuestionOption({{ $index }}, {{ $optionIndex }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                                        <x-ui.icon name="x" />
                                                    </x-ui.button>
                                                </div>
                                                @error('questions.'.$index.'.options.'.$optionIndex.'.name')
                                                    <p class="mt-1 text-xs font-medium text-destructive">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endforeach
                                        <x-ui.button variant="ghost" size="xs" wire:click="addQuestionOption({{ $index }})">
                                            <x-ui.icon name="plus" size="size-3.5" /> {{ __('Add option') }}
                                        </x-ui.button>
                                    </div>
                                @endif

                                @if ($question['type'] === 'score')
                                    <div class="space-y-2">
                                        <p class="text-xs text-muted-foreground">{{ __('Levels, ordered from lowest to highest (at least two)') }}</p>
                                        @foreach ($question['levels'] ?? [] as $levelIndex => $level)
                                            <div class="flex items-center gap-2" wire:key="question-{{ $index }}-level-{{ $levelIndex }}">
                                                <x-ui.input wire:model="questions.{{ $index }}.levels.{{ $levelIndex }}" placeholder="{{ __('e.g. Junior') }}" maxlength="64" />
                                                <x-ui.button variant="ghost" size="icon-sm" wire:click="removeQuestionLevel({{ $index }}, {{ $levelIndex }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                                    <x-ui.icon name="x" />
                                                </x-ui.button>
                                            </div>
                                        @endforeach
                                        <x-ui.button variant="ghost" size="xs" wire:click="addQuestionLevel({{ $index }})">
                                            <x-ui.icon name="plus" size="size-3.5" /> {{ __('Add level') }}
                                        </x-ui.button>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <x-ui.separator class="my-2" />
                        <p class="text-sm font-medium">{{ __('New question') }}</p>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <x-ui.input wire:model="newQuestionKey" placeholder="{{ __('Key (e.g. remote_ok)') }}" maxlength="64" />
                            <x-ui.select wire:model.live="newQuestionType">
                                <option value="boolean">{{ __('Boolean (yes / no)') }}</option>
                                <option value="choice">{{ __('Choice') }}</option>
                                <option value="score">{{ __('Score') }}</option>
                            </x-ui.select>
                        </div>
                        <x-ui.input wire:model="newQuestionText" placeholder="{{ __('Question (e.g. Does this job allow remote work?)') }}" maxlength="1000" />

                        @if ($newQuestionType === 'choice')
                            <div class="space-y-2">
                                <p class="text-xs text-muted-foreground">{{ __('Options (at least two)') }}</p>
                                @foreach ($newQuestionOptions as $optionIndex => $option)
                                    <div class="flex items-center gap-2" wire:key="new-option-{{ $optionIndex }}">
                                        <x-ui.input wire:model="newQuestionOptions.{{ $optionIndex }}.name" class="w-1/3" placeholder="{{ __('Name (e.g. hybrid)') }}" maxlength="64" />
                                        <x-ui.input wire:model="newQuestionOptions.{{ $optionIndex }}.description" placeholder="{{ __('Optional description') }}" maxlength="255" />
                                        <x-ui.button variant="ghost" size="icon-sm" wire:click="removeNewQuestionOption({{ $optionIndex }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                            <x-ui.icon name="x" />
                                        </x-ui.button>
                                    </div>
                                @endforeach
                                <x-ui.button variant="ghost" size="xs" wire:click="addNewQuestionOption">
                                    <x-ui.icon name="plus" size="size-3.5" /> {{ __('Add option') }}
                                </x-ui.button>
                            </div>
                        @endif

                        @if ($newQuestionType === 'score')
                            <div class="space-y-2">
                                <p class="text-xs text-muted-foreground">{{ __('Levels, ordered from lowest to highest (at least two)') }}</p>
                                @foreach ($newQuestionLevels as $levelIndex => $level)
                                    <div class="flex items-center gap-2" wire:key="new-level-{{ $levelIndex }}">
                                        <x-ui.input wire:model="newQuestionLevels.{{ $levelIndex }}" placeholder="{{ __('e.g. Junior') }}" maxlength="64" />
                                        <x-ui.button variant="ghost" size="icon-sm" wire:click="removeNewQuestionLevel({{ $levelIndex }})" aria-label="{{ __('Remove') }}" class="hover:text-destructive">
                                            <x-ui.icon name="x" />
                                        </x-ui.button>
                                    </div>
                                @endforeach
                                <x-ui.button variant="ghost" size="xs" wire:click="addNewQuestionLevel">
                                    <x-ui.icon name="plus" size="size-3.5" /> {{ __('Add level') }}
                                </x-ui.button>
                            </div>
                        @endif

                        <x-ui.button variant="secondary" wire:click="addQuestion">{{ __('Add question') }}</x-ui.button>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Default Cover Letter') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Choose which of your cover letters is used as the reference when generating a new cover letter for a job listing.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <x-ui.field error="defaultCoverLetter">
                            <x-ui.select wire:model="defaultCoverLetter" aria-label="{{ __('Default Cover Letter') }}">
                                <option value="">{{ __('No default cover letter') }}</option>
                                @foreach ($coverLetters as $letter)
                                    <option value="{{ $letter['id'] }}">{{ $letter['title'] }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Auto-match New Jobs') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('When enabled, new matches are queued automatically.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <label class="flex cursor-pointer items-center justify-between gap-4">
                            <span class="text-sm">{{ __('Fetch and auto-rate the newest 20 matching jobs each morning.') }}</span>
                            <x-ui.switch wire:model="autoMatchNewJobs" />
                        </label>
                    </x-ui.card-content>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>{{ __('Match Notifications') }}</x-ui.card-title>
                        <x-ui.card-description>{{ __('Only send emails when a score meets your threshold. Leave a field empty to ignore that score.') }}</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-4">
                        <x-ui.field :label="__('Notify when')" for="notifyMatchMode">
                            <x-ui.select id="notifyMatchMode" wire:model="notifyMatchMode">
                                <option value="any">{{ __('Any selected score meets its threshold') }}</option>
                                <option value="all">{{ __('All selected scores meet their thresholds') }}</option>
                            </x-ui.select>
                        </x-ui.field>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ([
                                'notifySkillsMatchThreshold' => __('Skills match threshold (%)'),
                                'notifyExperienceRelevanceThreshold' => __('Experience relevance threshold (%)'),
                                'notifySeniorityFitThreshold' => __('Seniority fit threshold (%)'),
                                'notifyKeywordMatchThreshold' => __('Keyword match threshold (%)'),
                            ] as $field => $label)
                                <x-ui.field :label="$label" :for="$field" :error="$field">
                                    <x-ui.input :id="$field" type="number" wire:model="{{ $field }}" min="0" max="100" step="0.1" placeholder="40" />
                                </x-ui.field>
                            @endforeach
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>

        {{-- Sticky save bar --}}
        <div class="fixed inset-x-0 bottom-0 z-30 border-t bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto flex max-w-7xl items-center justify-end gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <span wire:dirty class="mr-auto flex items-center gap-2 text-sm text-warning">
                    <span class="size-2 rounded-full bg-warning"></span> {{ __('Unsaved changes') }}
                </span>
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                    <x-ui.spinner wire:loading wire:target="save" />
                    <x-ui.icon name="save" wire:loading.remove wire:target="save" />
                    {{ __('Save Changes') }}
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
