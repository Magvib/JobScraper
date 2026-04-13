<?php

use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public ?string $cv = null;

    public ?array $keywords = null;

    public function mount()
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->cv = $user->cv;
        $this->keywords = $user->keywords;
    }
}
?>

<div class="py-8">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    @auth
                    <div class="avatar pointer-events-none">
                        <div class="ring-primary ring-offset-base-100 w-12 rounded-full ring-2 ring-offset-2 pointer-events-none select-none">
                            <img src="{{ auth()->user()->avatar }}" />
                        </div>
                    </div>
                    @endauth
                    <span>Welcome back, <span class="text-primary">{{ $username }}</span>!</span>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Here's your personalized job search dashboard</p>
            </div>
            
            <div class="flex gap-2">
                @if ($cv)
                    <a href="{{ route('jobs') }}" wire:navigate class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Jobs
                    </a>
                @else
                    <a href="{{ route('profile') }}" wire:navigate class="btn btn-warning gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload CV
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-lg border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Profile Status</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="badge {{ $cv ? 'badge-success' : 'badge-warning' }} gap-2">
                                    {{ $cv ? 'Complete' : 'Incomplete' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl">
                            @if ($cv)
                                ✅
                            @else
                                ⚠️
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        @if ($cv)
                            Your CV is uploaded and ready for job matching
                        @else
                            Upload your CV to get personalized job recommendations
                        @endif
                    </p>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-lg border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">AI Analysis</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="badge {{ $keywords ? 'badge-success' : 'badge-warning' }} gap-2">
                                    {{ $keywords ? 'Ready' : 'Pending' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl">
                            @if ($keywords)
                                🤖
                            @else
                                ⏳
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        @if ($keywords)
                            Your CV has been analyzed with {{ count($keywords) }} keywords extracted
                        @else
                            AI will analyze your skills once CV is uploaded
                        @endif
                    </p>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-lg border border-base-300">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Job Matching</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="badge {{ $keywords ? 'badge-success' : 'badge-secondary' }} gap-2">
                                    {{ $keywords ? 'Active' : 'Ready' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-3xl">
                            🎯
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-3">
                        @if ($keywords)
                            Your profile is being matched with relevant job opportunities
                        @else
                            Start by uploading your CV to enable job matching
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Progress Steps (Visual Timeline) -->
        <div class="card bg-base-100 shadow-lg mb-8">
            <div class="card-body">
                <h2 class="text-xl font-bold mb-4">Your Journey</h2>
                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>
                    
                    <!-- Step 1 -->
                    <div class="relative flex items-start mb-8">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center z-10">
                            1
                        </div>
                        <div class="ml-6">
                            <h3 class="font-semibold">Sign Up</h3>
                            <p class="text-sm text-gray-500">You're signed in with GitHub</p>
                            <div class="badge badge-success mt-1">Completed</div>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative flex items-start mb-8">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $cv ? 'bg-primary' : 'bg-base-300' }} {{ $cv ? 'text-primary-content' : 'text-base-content' }} flex items-center justify-center z-10">
                            2
                        </div>
                        <div class="ml-6">
                            <h3 class="font-semibold">Upload CV</h3>
                            <p class="text-sm text-gray-500">Upload your resume for AI analysis</p>
                            <div class="badge {{ $cv ? 'badge-success' : 'badge-warning' }} mt-1">
                                {{ $cv ? 'Completed' : 'Pending' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="relative flex items-start mb-8">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $keywords ? 'bg-primary' : 'bg-base-300' }} {{ $keywords ? 'text-primary-content' : 'text-base-content' }} flex items-center justify-center z-10">
                            3
                        </div>
                        <div class="ml-6">
                            <h3 class="font-semibold">AI Analysis</h3>
                            <p class="text-sm text-gray-500">AI extracts skills and keywords from your CV</p>
                            <div class="badge {{ $keywords ? 'badge-success' : ($cv ? 'badge-info' : 'badge-secondary') }} mt-1">
                                {{ $keywords ? 'Completed' : ($cv ? 'Processing' : 'Waiting') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="relative flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $keywords ? 'bg-primary' : 'bg-base-300' }} {{ $keywords ? 'text-primary-content' : 'text-base-content' }} flex items-center justify-center z-10">
                            4
                        </div>
                        <div class="ml-6">
                            <h3 class="font-semibold">Find Jobs</h3>
                            <p class="text-sm text-gray-500">Browse matched job opportunities</p>
                            <div class="badge {{ $keywords ? 'badge-success' : 'badge-secondary' }} mt-1">
                                {{ $keywords ? 'Ready' : 'Locked' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="text-lg font-semibold mb-4">Next Steps</h3>
                    <ul class="space-y-3">
                        @if (!$cv)
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-warning/20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">Upload your CV</p>
                                    <p class="text-sm text-gray-500">Get started with personalized job matching</p>
                                </div>
                            </li>
                        @endif
                        
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Browse job opportunities</p>
                                <p class="text-sm text-gray-500">Find positions matching your skills</p>
                            </div>
                        </li>
                        
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-info/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Update your profile</p>
                                <p class="text-sm text-gray-500">Keep your information current</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-success/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Signed in successfully</p>
                                <p class="text-sm text-gray-500">Just now via GitHub</p>
                            </div>
                        </div>
                        
                        @if ($cv)
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <div class="w-10 h-10 rounded-full bg-info/20 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">CV uploaded</p>
                                    <p class="text-sm text-gray-500">Ready for AI analysis</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>