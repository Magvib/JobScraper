<?php

use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public function mount()
    {
        $user = auth()->user();
        $this->username = $user->name;
    }
}
?>

<div class="py-10">
    <div class="max-w-7xl mx-auto card bg-base-100 shadow-sm mt-4">
        <div class="card-body">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-4">
                @auth
                <div class="avatar ml-2 pointer-events-none">
                    <div class="ring-primary ring-offset-base-100 w-8 rounded-full ring-2 ring-offset-2 pointer-events-none select-none">
                        <img
                            src="{{ auth()->user()->avatar }}" />
                    </div>
                </div>
                @endauth
                {{ __('Welcome') }} {{ $username }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('Get started by uploading your resume to let our AI analyze it and match you with relevant job opportunities.') }}</p>

            <div class="mt-4 mb-6 card bg-base-200/50 shadow-sm p-4">
                <ul class="steps">
                    <li class="step step-primary">{{ __('Login') }}</li>
                    <li class="step step-primary">{{ __('Upload CV') }}</li>
                    <li class="step">{{ __('Checking CV') }}</li>
                    <li class="step">{{ __('Find Jobs') }}</li>
                </ul>
            </div>

            <button class="btn btn-warning w-fit" wire:navigate href="{{ route('profile') }}">{{ __('Upload CV') }}</button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto mt-4 flex flex-col gap-2">
        <h2 class="text-lg font-bold px-1">{{ __('Frequently Asked Questions') }}</h2>

        <div class="collapse collapse-arrow bg-base-100">
            <input type="radio" name="my-accordion-1" checked="checked" />
            <div class="collapse-title font-semibold">{{ __('How does this work?') }}</div>
            <div class="collapse-content text-sm">{{ __('Upload your resume and our AI will analyse it, suggest improvements, match you with relevant jobs, and help you apply with one click.') }}</div>
        </div>
        <div class="collapse collapse-arrow bg-base-100">
            <input type="radio" name="my-accordion-1" />
            <div class="collapse-title font-semibold">{{ __('How do I get started?') }}</div>
            <div class="collapse-content text-sm">{{ __('You\'re already in! Head to the Jobs page from the navigation to browse positions matched to your profile, or visit your Profile page to upload or update your resume.') }}</div>
        </div>
        <div class="collapse collapse-arrow bg-base-100">
            <input type="radio" name="my-accordion-1" />
            <div class="collapse-title font-semibold">{{ __('What does the AI do with my resume?') }}</div>
            <div class="collapse-content text-sm">{{ __('Our AI reads your resume to understand your skills and experience. It then suggests improvements to strengthen it and uses that information to find jobs that are a strong match for your background.') }}</div>
        </div>
        <div class="collapse collapse-arrow bg-base-100">
            <input type="radio" name="my-accordion-1" />
            <div class="collapse-title font-semibold">{{ __('How does one-click apply work?') }}</div>
            <div class="collapse-content text-sm">{{ __('Once your resume is uploaded and your profile is complete, the AI can pre-fill application details for matching jobs so you can apply in a single click without repeating the same information every time.') }}</div>
        </div>
        <div class="collapse collapse-arrow bg-base-100">
            <input type="radio" name="my-accordion-1" />
            <div class="collapse-title font-semibold">{{ __('Why do I need to log in with GitHub?') }}</div>
            <div class="collapse-content text-sm">{{ __('GitHub login lets you sign in quickly and securely without needing a separate password. It also allows us to enrich your profile with your public developer activity when relevant.') }}</div>
        </div>
    </div>
</div>