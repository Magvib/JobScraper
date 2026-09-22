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

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto card bg-base-100 shadow-sm mt-4">
        <form wire:submit="save" class="mt-6 space-y-6">
            <div class="card-body flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-4 mb-2">
                        @auth
                        <div class="avatar ml-2">
                            <div class="ring-primary ring-offset-base-100 w-12 rounded-full ring-2 ring-offset-2">
                                <img src="{{ auth()->user()->image ? '/storage/' . auth()->user()->image : auth()->user()->avatar }}" />
                            </div>
                        </div>
                        @endauth
                        {{ __('Profile Settings') }}
                    </h1>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Username') }}</legend>
                        <input
                            type="text"
                            wire:model="username"
                            class="input validator w-full"
                            placeholder="{{ __('Enter your username') }}"
                            required
                            minlength="2"
                            maxlength="50" />
                        <p class="fieldset-label">{{ __('This is how you appear to others') }}</p>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Information') }}</legend>

                        <label class="label">
                            <span class="label-text">{{ __('Profile Image') }}</span>
                        </label>
                        @if ($image)
                        <div class="avatar mb-2">
                            <div class="w-16 rounded-full ring-2 ring-primary ring-offset-2 ring-offset-base-200">
                                <img src="{{ $image->temporaryUrl() }}" alt="{{ __('New profile image preview') }}" />
                            </div>
                        </div>
                        @elseif (auth()->user()->image)
                        <div class="avatar mb-2">
                            <div class="w-16 rounded-full ring-2 ring-primary ring-offset-2 ring-offset-base-200">
                                <img src="/storage/{{ auth()->user()->image }}" alt="{{ __('Profile image') }}" />
                            </div>
                        </div>
                        @endif
                        <input
                            type="file"
                            wire:model="image"
                            class="file-input file-input-bordered w-full"
                            accept="image/*" />
                        <p class="fieldset-label">{{ __('JPG or PNG up to 5MB') }}</p>
                        @error('image')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <label class="label mt-2">
                            <span class="label-text">{{ __('Email') }}</span>
                        </label>
                        <input
                            type="email"
                            wire:model="email"
                            class="input w-full"
                            placeholder="{{ __('name@p13.dk') }}"
                            required
                            maxlength="255" />
                        @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <label class="label mt-2">
                            <span class="label-text">{{ __('Phone Number') }}</span>
                        </label>
                        <input
                            type="tel"
                            wire:model="phone"
                            class="input w-full"
                            placeholder="{{ __('+45 12 34 56 78') }}"
                            maxlength="20" />
                        @error('phone')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <label class="label mt-2">
                            <span class="label-text">{{ __('Birthdate') }}</span>
                        </label>
                        <input
                            type="date"
                            wire:model="birthdate"
                            class="input w-full"
                            max="{{ now()->format('Y-m-d') }}" />
                        @error('birthdate')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <label class="label mt-2">
                            <span class="label-text">{{ __('Job Title') }}</span>
                        </label>
                        <input
                            type="text"
                            wire:model="jobTitle"
                            class="input w-full"
                            placeholder="{{ __('Software Developer') }}"
                            maxlength="100" />
                        @error('jobTitle')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Location') }}</legend>
                        <label class="label">
                            <span class="label-text">{{ __('Street Address') }}</span>
                        </label>
                        <input
                            type="text"
                            wire:model="address"
                            class="input w-full"
                            placeholder="{{ __('123 Main St') }}"
                            maxlength="255" />
                        <label class="label mt-2">
                            <span class="label-text">{{ __('Zip / Postal Code') }}</span>
                        </label>
                        <input
                            type="text"
                            wire:model="zip"
                            class="input w-full"
                            placeholder="{{ __('8800') }}"
                            maxlength="10" />
                        <label class="label mt-2">
                            <span class="label-text">{{ __('City') }}</span>
                        </label>
                        <input
                            type="text"
                            wire:model="city"
                            class="input w-full"
                            placeholder="{{ __('Viborg') }}"
                            maxlength="100" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Latitude') }}</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="latitude"
                                    class="input w-full"
                                    readonly
                                    placeholder="{{ __('Set automatically on save') }}" />
                            </div>
                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Longitude') }}</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="longitude"
                                    class="input w-full"
                                    readonly
                                    placeholder="{{ __('Set automatically on save') }}" />
                            </div>
                        </div>
                        <button type="button" wire:click="forceUpdateCords" class="btn btn-secondary mt-2">
                            {{ __('Update Coordinates') }}
                        </button>
                        <label class="label mt-2">
                            <span class="label-text">{{ __('Max Distance (km)') }}</span>
                        </label>
                        <input
                            type="number"
                            wire:model="maxDistance"
                            class="input w-full"
                            min="1"
                            max="500" />
                    </fieldset>
                </div>

                <div class="flex-1">
                    <div class="lg:mb-14"></div>
                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Search Keywords') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('These keywords are used to find matching jobs based on your CV. You can add or remove keywords.') }}</p>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($keywords as $index => $keyword)
                            <div class="badge badge-primary badge-lg gap-2">
                                {{ $keyword }}
                                <button type="button" wire:click="removeKeyword({{ $index }})" class="hover:text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <input
                                type="text"
                                wire:model="newKeyword"
                                wire:keydown.enter="addKeyword"
                                class="input input-bordered w-full"
                                placeholder="{{ __('Add a keyword') }}" />
                            <button type="button" wire:click="addKeyword" class="btn btn-secondary">
                                {{ __('Add') }}
                            </button>
                            <button type="button" wire:click="generateKeywords" class="btn">
                                {{ __('Generate from CV') }}
                            </button>
                        </div>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4 mt-4">
                        <legend class="fieldset-legend">{{ __('Skills') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('Add the skills you have. These are displayed on your CV.') }}</p>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($skills as $index => $skill)
                            <div class="badge badge-secondary badge-lg gap-2">
                                {{ $skill }}
                                <button type="button" wire:click="removeSkill({{ $index }})" class="hover:text-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <input
                                type="text"
                                wire:model="newSkill"
                                wire:keydown.enter="addSkill"
                                class="input input-bordered w-full"
                                placeholder="{{ __('Add a skill') }}" />
                            <button type="button" wire:click="addSkill" class="btn btn-secondary">
                                {{ __('Add') }}
                            </button>
                        </div>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4 mt-4">
                        <legend class="fieldset-legend">{{ __('Links') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('Add links to your GitHub, LinkedIn, portfolio and more. These are displayed on your CV.') }}</p>

                        <div class="flex flex-col gap-2 mb-3">
                            @foreach($links as $index => $link)
                            <div class="flex items-center justify-between gap-2 border border-base-300 rounded-box px-3 py-2">
                                <div class="min-w-0">
                                    <span class="font-semibold">{{ $link['name'] }}</span>
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="block text-sm text-base-content/60 truncate hover:text-primary">
                                        {{ $link['url'] }}
                                    </a>
                                </div>
                                <button type="button" wire:click="removeLink({{ $index }})" class="hover:text-error shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <input
                                type="text"
                                wire:model="newLinkName"
                                class="input input-bordered w-full"
                                placeholder="{{ __('Name (e.g. GitHub)') }}"
                                maxlength="255" />
                            <input
                                type="text"
                                wire:model="newLinkUrl"
                                wire:keydown.enter="addLink"
                                class="input input-bordered w-full"
                                placeholder="{{ __('URL (e.g. github.com/username)') }}"
                                maxlength="255" />
                        </div>
                        <button type="button" wire:click="addLink" class="btn btn-secondary mt-2">
                            {{ __('Add') }}
                        </button>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4 mt-4">
                        <legend class="fieldset-legend">{{ __('AI Questions') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('Define your own questions that the AI answers about each job posting. Answer them from a job with the Answer Questions button.') }}</p>

                        @foreach($questions as $index => $question)
                        <div class="border border-base-300 rounded-box px-3 py-2 mb-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="badge badge-outline">{{ $question['key'] }}</span>
                                <span class="badge badge-ghost">{{ __(Str::title($question['type'])) }}</span>
                                <button type="button" wire:click="removeQuestion({{ $index }})" class="hover:text-error shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <input
                                type="text"
                                wire:model="questions.{{ $index }}.question"
                                class="input input-bordered w-full mt-2"
                                maxlength="1000"
                                placeholder="{{ __('Question') }}" />
                            @error('questions.'.$index.'.question')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @if($question['type'] === 'choice')
                            <div class="mt-2 space-y-2">
                                <p class="text-sm text-base-content/60">{{ __('Options (at least two)') }}</p>
                                @foreach($question['options'] ?? [] as $optionIndex => $option)
                                <div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="text"
                                            wire:model="questions.{{ $index }}.options.{{ $optionIndex }}.name"
                                            class="input input-bordered w-1/3 @error('questions.'.$index.'.options.'.$optionIndex.'.name') input-error @enderror"
                                            placeholder="{{ __('Name (e.g. hybrid)') }}"
                                            maxlength="64" />
                                        <input
                                            type="text"
                                            wire:model="questions.{{ $index }}.options.{{ $optionIndex }}.description"
                                            class="input input-bordered w-full"
                                            placeholder="{{ __('Optional description') }}"
                                            maxlength="255" />
                                        <button type="button" wire:click="removeQuestionOption({{ $index }}, {{ $optionIndex }})" class="hover:text-error shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    @error('questions.'.$index.'.options.'.$optionIndex.'.name')
                                    <p class="text-error text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                                @endforeach
                                <button type="button" wire:click="addQuestionOption({{ $index }})" class="btn btn-ghost btn-sm">
                                    {{ __('+ Add option') }}
                                </button>
                            </div>
                            @endif
                            @if($question['type'] === 'score')
                            <div class="mt-2 space-y-2">
                                <p class="text-sm text-base-content/60">{{ __('Levels, ordered from lowest to highest (at least two)') }}</p>
                                @foreach($question['levels'] ?? [] as $levelIndex => $level)
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        wire:model="questions.{{ $index }}.levels.{{ $levelIndex }}"
                                        class="input input-bordered w-full"
                                        placeholder="{{ __('e.g. Junior') }}"
                                        maxlength="64" />
                                    <button type="button" wire:click="removeQuestionLevel({{ $index }}, {{ $levelIndex }})" class="hover:text-error shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                                <button type="button" wire:click="addQuestionLevel({{ $index }})" class="btn btn-ghost btn-sm">
                                    {{ __('+ Add level') }}
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <input
                                type="text"
                                wire:model="newQuestionKey"
                                class="input input-bordered w-full"
                                placeholder="{{ __('Key (e.g. remote_ok)') }}"
                                maxlength="64" />
                            <select wire:model.live="newQuestionType" class="select select-bordered w-full">
                                <option value="boolean">{{ __('Boolean (yes / no)') }}</option>
                                <option value="choice">{{ __('Choice') }}</option>
                                <option value="score">{{ __('Score') }}</option>
                            </select>
                        </div>
                        <input
                            type="text"
                            wire:model="newQuestionText"
                            class="input input-bordered w-full mt-2"
                            placeholder="{{ __('Question (e.g. Does this job allow remote work?)') }}"
                            maxlength="1000" />
                        @if($newQuestionType === 'choice')
                        <div class="mt-2 space-y-2">
                            <p class="text-sm text-base-content/60">{{ __('Options (at least two)') }}</p>
                            @foreach($newQuestionOptions as $optionIndex => $option)
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model="newQuestionOptions.{{ $optionIndex }}.name"
                                    class="input input-bordered w-1/3"
                                    placeholder="{{ __('Name (e.g. hybrid)') }}"
                                    maxlength="64" />
                                <input
                                    type="text"
                                    wire:model="newQuestionOptions.{{ $optionIndex }}.description"
                                    class="input input-bordered w-full"
                                    placeholder="{{ __('Optional description') }}"
                                    maxlength="255" />
                                <button type="button" wire:click="removeNewQuestionOption({{ $optionIndex }})" class="hover:text-error shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                            <button type="button" wire:click="addNewQuestionOption" class="btn btn-ghost btn-sm">
                                {{ __('+ Add option') }}
                            </button>
                        </div>
                        @endif
                        @if($newQuestionType === 'score')
                        <div class="mt-2 space-y-2">
                            <p class="text-sm text-base-content/60">{{ __('Levels, ordered from lowest to highest (at least two)') }}</p>
                            @foreach($newQuestionLevels as $levelIndex => $level)
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model="newQuestionLevels.{{ $levelIndex }}"
                                    class="input input-bordered w-full"
                                    placeholder="{{ __('e.g. Junior') }}"
                                    maxlength="64" />
                                <button type="button" wire:click="removeNewQuestionLevel({{ $levelIndex }})" class="hover:text-error shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                            <button type="button" wire:click="addNewQuestionLevel" class="btn btn-ghost btn-sm">
                                {{ __('+ Add level') }}
                            </button>
                        </div>
                        @endif
                        <button type="button" wire:click="addQuestion" class="btn btn-secondary mt-2">
                            {{ __('Add question') }}
                        </button>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4 mt-4">
                        <legend class="fieldset-legend">{{ __('Default Cover Letter') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('Choose which of your cover letters is used as the reference when generating a new cover letter for a job listing.') }}</p>

                        <select wire:model="defaultCoverLetter" class="select select-bordered w-full">
                            <option value="">{{ __('No default cover letter') }}</option>
                            @foreach($coverLetters as $letter)
                            <option value="{{ $letter['id'] }}">{{ $letter['title'] }}</option>
                            @endforeach
                        </select>
                        @error('defaultCoverLetter')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Auto-match New Jobs') }}</legend>
                        <label class="label cursor-pointer justify-between gap-4">
                            <span class="label-text">{{ __('Fetch and auto-rate the newest 20 matching jobs each morning.') }}</span>
                            <input type="checkbox" wire:model="autoMatchNewJobs" class="toggle toggle-primary" />
                        </label>
                        <p class="fieldset-label">{{ __('When enabled, new matches are queued automatically.') }}</p>
                    </fieldset>

                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                        <legend class="fieldset-legend">{{ __('Match Notifications') }}</legend>
                        <p class="text-sm text-base-content/60 mb-3">{{ __('Only send emails when a score meets your threshold. Leave a field empty to ignore that score.') }}</p>

                        <label class="label">
                            <span class="label-text">{{ __('Notify when') }}</span>
                        </label>
                        <select wire:model="notifyMatchMode" class="select select-bordered w-full">
                            <option value="any">{{ __('Any selected score meets its threshold') }}</option>
                            <option value="all">{{ __('All selected scores meet their thresholds') }}</option>
                        </select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Skills match threshold (%)') }}</span>
                                </label>
                                <input
                                    type="number"
                                    wire:model="notifySkillsMatchThreshold"
                                    class="input w-full"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    placeholder="40" />
                            </div>

                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Experience relevance threshold (%)') }}</span>
                                </label>
                                <input
                                    type="number"
                                    wire:model="notifyExperienceRelevanceThreshold"
                                    class="input w-full"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    placeholder="40" />
                            </div>

                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Seniority fit threshold (%)') }}</span>
                                </label>
                                <input
                                    type="number"
                                    wire:model="notifySeniorityFitThreshold"
                                    class="input w-full"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    placeholder="40" />
                            </div>

                            <div>
                                <label class="label">
                                    <span class="label-text">{{ __('Keyword match threshold (%)') }}</span>
                                </label>
                                <input
                                    type="number"
                                    wire:model="notifyKeywordMatchThreshold"
                                    class="input w-full"
                                    min="0"
                                    max="100"
                                    step="0.1"
                                    placeholder="40" />
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex items-center justify-end gap-3 mt-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>