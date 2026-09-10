<?php

use App\Ai\Agents\KeywordSpecialist;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Document;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $username = '';

    public string $address = '';

    public string $zip = '';

    public string $city = '';

    public int $maxDistance = 50;

    public $cvFile = null;

    public ?string $existingCv = null;

    public string $email = '';

    public string $phone = '';

    public ?string $birthdate = null;

    public string $jobTitle = '';

    public $image = null;

    public array $keywords = [];

    public string $newKeyword = '';

    public bool $autoMatchNewJobs = false;

    public ?float $notifySkillsMatchThreshold = null;

    public ?float $notifyExperienceRelevanceThreshold = null;

    public ?float $notifySeniorityFitThreshold = null;

    public ?float $notifyKeywordMatchThreshold = null;

    public string $notifyMatchMode = 'any';

    public array $jobs = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->address = $user->address ?? '';
        $this->zip = $user->zip ?? '';
        $this->city = $user->city ?? '';
        $this->maxDistance = $user->max_distance ?? 50;
        $this->existingCv = $user->cv;
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->birthdate = $user->birthdate?->format('Y-m-d');
        $this->jobTitle = $user->job_title ?? '';
        $this->keywords = $user->keywords ?? [];
        $this->autoMatchNewJobs = (bool) $user->auto_match_new_jobs;
        $this->notifySkillsMatchThreshold = $user->notify_skills_match_threshold;
        $this->notifyExperienceRelevanceThreshold = $user->notify_experience_relevance_threshold;
        $this->notifySeniorityFitThreshold = $user->notify_seniority_fit_threshold;
        $this->notifyKeywordMatchThreshold = $user->notify_keyword_match_threshold;
        $this->notifyMatchMode = $user->notify_match_mode ?? 'any';
        $this->jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];

        // Sort jobs with startDate, endDate, title in a ascending order
        usort($this->jobs, function ($a, $b) {
            // Check that they have startDate and endDate
            if (!isset($a['startDate'], $a['endDate'], $b['startDate'], $b['endDate'])) {
                return 0;
            }
            
            return strtotime($a['startDate']) <=> strtotime($b['startDate'])
                ?: strtotime($a['endDate']) <=> strtotime($b['endDate'])
                ?: strcmp($a['title'], $b['title']);
        });
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
            'cvFile' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'autoMatchNewJobs' => ['boolean'],
            'notifySkillsMatchThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyExperienceRelevanceThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifySeniorityFitThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyKeywordMatchThreshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notifyMatchMode' => ['required', 'in:any,all'],
        ]);

        $user = auth()->user();
        $user->name = $this->username;
        $user->email = $this->email;
        $user->address = $this->address ?: null;
        $user->zip = $this->zip ?: null;
        $user->city = $this->city ?: null;
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

        if ($this->cvFile) {
            if ($user->cv) {
                Storage::disk('local')->delete($user->cv);
            }

            $path = $this->cvFile->store('cv', 'local');
            $user->cv = $path;
            $this->existingCv = $path;

            if (Storage::disk('local')->exists($this->existingCv)) {
                $keywords = (new KeywordSpecialist)->prompt('Give me the keywords for this CV',
                    attachments: [
                        Document::fromStorage($this->existingCv),
                    ]
                );
                $this->keywords = $keywords->structured['keywords'] ?? [];
            }
        }

        $user->keywords = $this->keywords;
        $user->save();
        $this->dispatch('toast',
            message: __('Profile saved successfully.'),
            type: 'success'
        );
    }

    public function showCV(): void
    {
        if ($this->existingCv) {
            $url = Storage::temporaryUrl($this->existingCv, now()->addMinutes(5));
            $this->js("window.open('{$url}', '_blank')");
        }
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

    public function generateKeywords(): void
    {
        if (! $this->existingCv) {
            $this->addError('cvFile', __('Upload a CV to generate keywords.'));
            return;
        }

        if (! Storage::disk('local')->exists($this->existingCv)) {
            $this->addError('cvFile', __('We could not find your CV file. Please re-upload it.'));
            return;
        }

        $keywords = (new KeywordSpecialist)->prompt('Give me the keywords for this CV',
            attachments: [
                Document::fromStorage($this->existingCv),
            ]
        );
        $this->keywords = $keywords->structured['keywords'] ?? [];

        $this->dispatch('toast',
            message: __('Keywords are being generated. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function saveCv(array $jobs): void
    {
        $user = auth()->user();
        $user->cv_json = json_encode($jobs);
        $user->save();
        $this->dispatch('toast',
            message: __('CV saved successfully.'),
            type: 'success'
        );
    }
}
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto card bg-base-100 shadow-sm mt-4">
        <form wire:submit="save" class="mt-6 space-y-6">
        <div class="card-body flex flex-col lg:flex-row gap-4">
            <div>
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
                        maxlength="50"
                    />
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
                        accept="image/*"
                    />
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
                        maxlength="255"
                    />
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
                        maxlength="20"
                    />
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
                        max="{{ now()->format('Y-m-d') }}"
                    />
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
                        maxlength="100"
                    />
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
                        maxlength="255"
                    />
                    <label class="label mt-2">
                        <span class="label-text">{{ __('Zip / Postal Code') }}</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model="zip" 
                        class="input w-full" 
                        placeholder="{{ __('8800') }}"
                        maxlength="10"
                    />
                    <label class="label mt-2">
                        <span class="label-text">{{ __('City') }}</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model="city" 
                        class="input w-full" 
                        placeholder="{{ __('Viborg') }}"
                        maxlength="100"
                    />
                    <label class="label mt-2">
                        <span class="label-text">{{ __('Max Distance (km)') }}</span>
                    </label>
                    <input 
                        type="number" 
                        wire:model="maxDistance" 
                        class="input w-full" 
                        min="1"
                        max="500"
                    />
                </fieldset>

                <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
                    <legend class="fieldset-legend">{{ __('Resume / CV') }}</legend>
                    
                    @if ($existingCv)
                        <div class="mb-4 flex items-center gap-3 p-3 bg-base-100 rounded-lg">
                            <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="font-medium">{{ __('CV uploaded') }}</p>
                                <p class="text-sm text-base-content/60">{{ Str::afterLast($existingCv, '/') }}</p>
                            </div>
                            <button type="button" wire:click="showCV" class="btn btn-sm btn-ghost">
                                {{ __('View') }}
                            </button>
                        </div>
                    @endif

                    <input 
                        type="file" 
                        wire:model="cvFile" 
                        class="file-input file-input-bordered w-full"
                        accept=".pdf,.doc,.docx"
                    />
                    <p class="fieldset-label">{{ __('PDF, DOC, or DOCX up to 10MB') }}</p>
                    
                    @error('cvFile')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4" x-data='{
                    jobs: @json($jobs),
                    init() {
                        console.log(9);
                    },
                    addJobSection() {
                        this.jobs.push({
                            title: "",
                            company: "",
                            startDate: "",
                            endDate: "",
                            description: ""
                        });
                    }
                }'>
                    <legend class="fieldset-legend">{{ __('CV Creator') }}</legend>

                    <template x-for="(job, index) in jobs" :key="index">
                        <div class="mb-4">
                            <input type="text" x-model="job.title" placeholder="Job Title" class="input input-bordered w-full mb-2" />
                            <input type="text" x-model="job.company" placeholder="Company" class="input input-bordered w-full mb-2" />
                            <input type="date" x-model="job.startDate" placeholder="Start Date" class="input input-bordered w-full mb-2" />
                            <input type="date" x-model="job.endDate" placeholder="End Date" class="input input-bordered w-full mb-2" />
                            <textarea x-model="job.description" placeholder="Description" class="textarea textarea-bordered w-full mb-2"></textarea>
                            <button type="button" @click="jobs.splice(index, 1)" class="btn btn-error">Remove</button>
                        </div>
                    </template>
                    <div class="flex justify-end mb-4">
                        <button type="button" @click="addJobSection()" class="btn btn-primary">Add Job</button>
                        <button type="button" wire:click="saveCv(jobs)" class="btn btn-warning ml-2">Save CV</button>
                    </div>
                </fieldset>
            </div>

            <div>
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
                            placeholder="{{ __('Add a keyword') }}"
                        />
                        <button type="button" wire:click="addKeyword" class="btn btn-secondary">
                            {{ __('Add') }}
                        </button>
                        <button type="button" wire:click="generateKeywords" class="btn">
                            {{ __('Generate from CV') }}
                        </button>
                    </div>
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
                                placeholder="40"
                            />
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
                                placeholder="40"
                            />
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
                                placeholder="40"
                            />
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
                                placeholder="40"
                            />
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
