<?php

use App\Ai\Agents\KeywordSpecialist;
use Illuminate\Support\Facades\Storage;
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

    public bool $saved = false;

    public array $keywords = [];

    public string $newKeyword = '';

    public bool $autoMatchNewJobs = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->address = $user->address ?? '';
        $this->zip = $user->zip ?? '';
        $this->city = $user->city ?? '';
        $this->maxDistance = $user->max_distance ?? 50;
        $this->existingCv = $user->cv;
        $this->keywords = $user->keywords ?? [];
        $this->autoMatchNewJobs = (bool) $user->auto_match_new_jobs;
    }

    public function save(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'min:2', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
            'maxDistance' => ['required', 'integer', 'min:1', 'max:500'],
            'cvFile' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'autoMatchNewJobs' => ['boolean'],
        ]);

        $user = auth()->user();
        $user->name = $this->username;
        $user->address = $this->address ?: null;
        $user->zip = $this->zip ?: null;
        $user->city = $this->city ?: null;
        $user->max_distance = $this->maxDistance;
        $user->auto_match_new_jobs = $this->autoMatchNewJobs;

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
        $this->saved = true;
        $this->dispatch('saved');
    }

    public function showCV(): void
    {
        if ($this->existingCv) {
            $url = Storage::temporaryUrl($this->existingCv, now()->addMinutes(5));
            // redirect()->away($url);
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
}
?>

<div class="py-10">
    <div class="max-w-2xl mx-auto card bg-base-100 shadow-sm mt-4">
        <div class="card-body">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-4">
                @auth
                <div class="avatar ml-2">
                    <div class="ring-primary ring-offset-base-100 w-12 rounded-full ring-2 ring-offset-2">
                        <img src="{{ auth()->user()->avatar }}" />
                    </div>
                </div>
                @endauth
                {{ __('Profile Settings') }}
            </h1>

            @if ($saved)
                <div class="alert alert-success mt-4">
                    <span>{{ __('Profile updated successfully!') }}</span>
                </div>
            @endif

            <form wire:submit="save" class="mt-6 space-y-6">
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

                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
