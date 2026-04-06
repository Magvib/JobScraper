<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
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

    public function mount(): void
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->address = $user->address ?? '';
        $this->zip = $user->zip ?? '';
        $this->city = $user->city ?? '';
        $this->maxDistance = $user->max_distance ?? 50;
        $this->existingCv = $user->cv;
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
        ]);

        $user = auth()->user();
        $user->name = $this->username;
        $user->address = $this->address ?: null;
        $user->zip = $this->zip ?: null;
        $user->city = $this->city ?: null;
        $user->max_distance = $this->maxDistance;
        
        if ($this->cvFile) {
            if ($user->cv) {
                Storage::disk('public')->delete($user->cv);
            }

            $path = $this->cvFile->store('cv', 'public');
            $user->cv = $path;
            $this->existingCv = $path;
        }

        $user->save();
        $this->saved = true;
        $this->dispatch('saved');
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
                            <a href="{{ Storage::url($existingCv) }}" target="_blank" class="btn btn-sm btn-ghost">
                                {{ __('View') }}
                            </a>
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

                <div class="flex items-center justify-end gap-3">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
