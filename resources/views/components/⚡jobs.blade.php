<?php

use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use App\Models\Post;
use App\Models\PostSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    // https://www.jobindex.dk/api/jobsearch/v3/jobcount?subid=1&radius=60&address=Svinglen+24%2C+8800+Viborg&q=php
    // https://www.jobindex.dk/api/jobsearch/v3?q=php&radius=60&address=Svinglen+24%2C+8800+Viborg
    // https://jobnet.dk/bff/FindJob/Search?resultsPerPage=20&pageNumber=1&orderType=BestMatch&searchString=php

    public $jobs;

    public string $search = '';

    public string $sort = 'date';

    public array $selectedKeywords = [];

    public array $selectedSources = [];

    public bool $showMap = false;

    public array $cordsForCitys = [
        "Copenhagen" => [55.6761, 12.5689],
        "Aarhus" => [56.1564, 10.2097],
        "Odense" => [55.4000, 10.3833],
        "Aalborg" => [57.0500, 9.9167],
        "Esbjerg" => [55.4708, 8.4514],
        "Randers" => [56.4569, 10.0392],
        "Horsens" => [55.8619, 9.8519],
        "Kolding" => [55.4908, 9.4722],
        "Vejle" => [55.7083, 9.5333],
        "Roskilde" => [55.6417, 12.0808],
        "Silkeborg" => [56.1667, 9.5500],
        "Herning" => [56.1361, 8.9767],
        "Hørsholm" => [55.8786, 12.4992],
        "Helsingør" => [56.0360, 12.6106],
        "Næstved" => [55.2333, 11.7667],
        "Viborg" => [56.4500, 9.4000],
        "Fredericia" => [55.5644, 9.7597],
        "Køge" => [55.4500, 12.1833],
        "Taastrup" => [55.6500, 12.3000],
        "Holstebro" => [56.3581, 8.6175],
        "Hillerød" => [55.9333, 12.3000],
        "Slagelse" => [55.4008, 11.3500],
        "Holbæk" => [55.7181, 11.7103],
        "Sønderborg" => [54.9114, 9.7867],
        "Svendborg" => [55.0597, 10.6100],
        "Hjørring" => [57.4600, 9.9867],
        "Ringsted" => [55.4406, 11.7936],
        "Nørresundby" => [57.0583, 9.9228],
        "Frederikshavn" => [57.4339, 10.5361],
        "Haderslev" => [55.2500, 9.5000],
        "Birkerød" => [55.8474, 12.4280],
        "Farum" => [55.8083, 12.3581],
        "Skanderborg" => [56.0333, 9.9333],
        "Skive" => [56.5667, 9.0269],
        "Smørumnedre" => [55.7380, 12.3020],
        "Nyborg" => [55.3097, 10.7917],
        "Solrød Strand" => [55.5319, 12.2194],
        "Frederikssund" => [55.8406, 12.0637],
        "Ikast" => [56.1356, 9.1547],
        "Lillerød" => [55.8708, 12.3560],
        "Middelfart" => [55.5058, 9.7306],
        "Hedehusene" => [55.6500, 12.1986],
        "Kalundborg" => [55.6814, 11.0850],
        "Aabenraa" => [55.0447, 9.4195],
        "Nykøbing Falster" => [54.7667, 11.8833],
        "Korsør" => [55.3283, 11.1397],
        "Varde" => [55.6200, 8.4806],
        "Grenaa" => [56.4133, 10.8758],
        "Rønne" => [55.1000, 14.7000],
        "Odder" => [55.9725, 10.1497],
        "Thisted" => [56.9539, 8.6903],
        "Hedensted" => [55.7725, 9.7017],
        "Værløse" => [55.7828, 12.3700],
        "Brønderslev" => [57.2651, 9.9599],
        "Frederiksværk" => [55.9699, 12.0199],
        "Haslev" => [55.3269, 11.9636],
        "Hobro" => [56.6333, 9.8000],
        "Dragør" => [55.5925, 12.6722],
        "Nakskov" => [54.8333, 11.1500],
        "Vordingborg" => [55.0083, 11.9103],
        "Jyllinge" => [55.7514, 12.1039],
        "Vejen" => [55.4758, 9.1381],
        "Lystrup" => [56.2456, 10.2347],
        "Struer" => [56.4917, 8.5944],
        "Ringkøbing" => [56.0833, 8.2500],
        "Humlebæk" => [55.9600, 12.5328],
        "Helsinge" => [56.0219, 12.1994],
        "Støvring" => [56.8890, 9.8310],
        "Galten" => [56.1564, 9.9050],
        "Sæby" => [57.3327, 10.5267],
        "Fredensborg" => [55.9738, 12.4051],
        "Nykøbing Mors" => [56.7981, 8.8622],
        "Nivå" => [55.9340, 12.5048],
        "Aars" => [56.8031, 9.5128],
        "Måløv" => [55.7500, 12.3333],
        "Løgten" => [56.2786, 10.3158],
        "Hundested" => [55.9636, 11.8525],
        "Hadsten" => [56.3256, 10.0485],
        "Hørning" => [56.0850, 10.0364],
        "Hinnerup" => [56.2644, 10.0633],
        "Sorø" => [55.4366, 11.5592],
        "Ribe" => [55.3272, 8.7631],
        "Svenstrup" => [56.9711, 9.8467],
        "Skjern" => [55.9436, 8.4978],
        "Ry" => [56.0922, 9.7581],
        "Billund" => [55.7243, 9.1188],
        "Tønder" => [54.9428, 8.8639],
        "Bjerringbro" => [56.3756, 9.6550],
        "Vojens" => [55.2483, 9.3050],
        "Skagen" => [57.7222, 10.5878],
        "Ebeltoft" => [56.2000, 10.6800],
        "Bramming" => [55.4650, 8.7044],
        "Hammel" => [56.2500, 9.8667],
        "Slangerup" => [55.8467, 12.1761],
        "Ringe" => [55.2369, 10.4794],
        "Fåborg" => [55.0950, 10.2422],
        "Gilleleje" => [56.1225, 12.3081],
        "Hornslet" => [56.3150, 10.3192],
        "Aabybro" => [57.1625, 9.7306],
        "Børkop" => [55.6419, 9.6519],
        "Skælskør" => [55.2528, 11.2931],
        "Rødekro" => [55.0701, 9.3350],
        "Kerteminde" => [55.4490, 10.6590],
        "Assens" => [55.2664, 9.8968],
        "Bellinge" => [55.3350, 10.3133],
        "Maribo" => [54.7753, 11.5035],
        "Hellebæk" => [56.0671, 12.5593],
        "Nordborg" => [55.0577, 9.7476],
        "Nibe" => [56.9833, 9.6333],
        "Tune" => [55.5931, 12.1708],
        "Klarup" => [57.0125, 10.0597],
        "Munkebo" => [55.4570, 10.5530],
        "Hirtshals" => [57.5900, 9.9600],
        "Strib" => [55.5367, 9.7722],
        "Otterup" => [55.5167, 10.4000],
        "Kjellerup" => [56.2847, 9.4331],
        "Fensmark" => [55.2778, 11.8050],
        "Hornbæk" => [56.0875, 12.4587],
        "Mårslet" => [56.0683, 10.1617],
        "Viby" => [55.5475, 12.0250],
        "Strøby Egede" => [55.4131, 12.2456],
        "Hadsund" => [56.7185, 10.1155],
        "Borup" => [55.4983, 11.9781],
        "Nykøbing Sjælland" => [55.9225, 11.6686],
        "Vamdrup" => [55.4270, 9.2830],
        "Kirke Hvalsø" => [55.5958, 11.8616],
        "Solbjerg" => [56.0397, 10.0867],
        "Vodskov" => [57.1083, 10.0269],
        "Brørup" => [55.4825, 9.0158],
        "Havdrup" => [55.5433, 12.1181],
        "Sakskøbing" => [54.7975, 11.6364],
        "Hjallerup" => [57.1675, 10.1450],
        "Rudkøbing" => [54.9380, 10.7160],
        "Langeskov" => [55.3568, 10.5862],
        "Årslev" => [55.3029, 10.4642],
        "Høng" => [55.5044, 11.2919],
        "Videbæk" => [56.0931, 8.6325],
        "Jyderup" => [55.6575, 11.4014],
        "Gråsten" => [54.9211, 9.5944],
        "Svogerslev" => [55.6339, 12.0111],
        "Jægerspris" => [55.8500, 11.9910],
        "Lynge" => [55.8397, 12.2767],
        "Fakse" => [55.2544, 12.1181],
        "Vildbjerg" => [56.1972, 8.7667],
        "Tarm" => [55.9053, 8.5206],
        "Dianalund" => [55.5295, 11.4924],
        "Jelling" => [55.7558, 9.4194],
        "Bogense" => [55.5642, 10.0894],
        "Tølløse" => [55.6117, 11.7736],
        "Harlev" => [56.1447, 9.9969],
        "Juelsminde" => [55.7062, 10.0155],
        "Løgstør" => [56.9667, 9.2500],
        "Brædstrup" => [55.9717, 9.6114],
        "Assentoft" => [56.4403, 10.1467],
        "Præstø" => [55.1167, 12.0500],
        "Virklund" => [56.1297, 9.5600],
        "Stege" => [54.9861, 12.2856],
        "Stenløse" => [55.3392, 10.3617],
        "Gistrup" => [56.9964, 9.9906],
        "Græsted" => [56.0653, 12.2842],
        "Nexø" => [55.0625, 15.1319],
        "Store Heddinge" => [55.3086, 12.3869],
        "Hjortshøj" => [56.2492, 10.2656],
        "Taulov" => [55.5364, 9.6083],
        "Sabro" => [56.2133, 10.0344],
        "Storvorde" => [57.0028, 10.1008],
        "Trige" => [56.2533, 10.1475],
        "Auning" => [56.4306, 10.3767],
        "Rønde" => [56.3000, 10.4833],
        "Søndersø" => [55.4858, 10.2549],
        "Glamsbjerg" => [55.2778, 10.1056],
        "Vissenbjerg" => [55.3862, 10.1317],
        "Årup" => [55.3798, 10.0482],
        "Bjæverskov" => [55.4575, 12.0319],
        "Thurø By" => [55.0456, 10.6672],
        "Broager" => [54.8890, 9.6696],
        "Vallensbæk Strand" => [55.6353, 12.3648],
        "Frederiksberg" => [55.6785, 12.5221],
        "Herlev" => [55.7235, 12.4404],
        "Kongens Lyngby" => [55.7718, 12.5060],
        "Søborg" => [55.7302, 12.5098],
        "Hvidovre" => [55.6503, 12.4758],
        "Rødovre" => [55.6827, 12.4644],
        "Charlottenlund" => [55.7537, 12.5918],
        "Ballerup" => [55.7198, 12.3520],
        "Glostrup" => [55.6666, 12.4038],
        "Brøndby" => [55.6541, 12.4215],
        "Albertslund" => [55.6623, 12.3351],
        "Ishøj" => [55.6184, 12.3281],
        "Allerød" => [55.8703, 12.3574],
        "Holte" => [55.8167, 12.4667],
        "Greve" => [55.5966, 12.2492],
        "Kokkedal" => [55.9098, 12.5152],
        "Kastrup" => [55.6352, 12.6489],
        "Stenløse" => [55.7677, 12.1960],
        "Ærøskøbing" => [54.8912, 10.4083],
    ];

    public function mount()
    {
        $user = auth()->user();
        Post::searchForJobs($user);
        $this->jobs = Post::query()->active()->whereIn('keyword', $user->keywords)->get();
    }

    public function refreshJobs()
    {
        $user = auth()->user();
        Post::searchForJobs($user, true);
        $this->jobs = Post::query()->active()->whereIn('keyword', $user->keywords)->get();
    }

    #[Computed]
    public function keywords()
    {
        return collect($this->jobs)
            ->pluck('keyword')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function toggleKeyword(string $keyword): void
    {
        if (($index = array_search($keyword, $this->selectedKeywords)) !== false) {
            unset($this->selectedKeywords[$index]);
        } else {
            $this->selectedKeywords[] = $keyword;
        }

        $this->selectedKeywords = array_values($this->selectedKeywords);
    }

    #[Computed]
    public function sources()
    {
        return collect($this->jobs)
            ->pluck('source')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function toggleSource(string $source): void
    {
        if (($index = array_search($source, $this->selectedSources)) !== false) {
            unset($this->selectedSources[$index]);
        } else {
            $this->selectedSources[] = $source;
        }

        $this->selectedSources = array_values($this->selectedSources);
    }

    public function toggleMap(): void
    {
        $this->showMap = ! $this->showMap;
    }

    protected array $cityAliases = [
        'københavn' => 'Copenhagen',
        'århus' => 'Aarhus',
    ];

    protected function cityCoordinates(?string $city): ?array
    {
        if (! $city) {
            return null;
        }

        $city = mb_strtolower(trim($city));

        // Map every known name (and alias) to its entry in $cordsForCitys
        $names = [];
        foreach ($this->cordsForCitys as $name => $coords) {
            $names[mb_strtolower($name)] = $name;
        }
        foreach ($this->cityAliases as $alias => $canonical) {
            $names[mb_strtolower($alias)] = $canonical;
        }

        // Exact match
        if (isset($names[$city])) {
            return $this->cordsForCitys[$names[$city]];
        }

        // Otherwise fall back to the longest matching prefix ("København Ø" -> "København").
        // Require a word boundary after the prefix so "Helsingør" does not match "Helsinge".
        $best = null;

        foreach ($names as $name => $canonical) {
            if ($name === '' || ! str_starts_with($city, $name)) {
                continue;
            }

            $remainder = mb_substr($city, mb_strlen($name));

            if ($remainder !== '' && $remainder[0] !== ' ') {
                continue;
            }

            if ($best === null || mb_strlen($name) > mb_strlen($best)) {
                $best = $name;
            }
        }
        
        return $best !== null ? $this->cordsForCitys[$names[$best]] : null;
    }

    public function hasMapPoint($job): bool
    {
        if ($job->latitude && $job->longitude) {
            return true;
        }

        return $this->cityCoordinates($job->city) !== null;
    }

    #[Computed]
    public function mapPoints()
    {
        return $this->sortedJobs
            ->map(function ($job) {
                $lat = $job->latitude;
                $lng = $job->longitude;

                if ((! $lat || ! $lng) && $job->city) {
                    $coords = $this->cityCoordinates($job->city);

                    if ($coords !== null) {
                        [$lat, $lng] = $coords;
                    }
                }

                if (! $lat || ! $lng) {
                    return null;
                }

                return [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'title' => $job->title,
                    'company' => $job->company_name,
                    'location' => $job->getLocation(),
                    'url' => $job->canonical_url,
                ];
            })
            ->filter()
            ->values();
    }

    #[Computed]
    public function sortedJobs()
    {
        $jobs = collect($this->jobs);

        if ($this->selectedKeywords !== []) {
            $jobs = $jobs->filter(fn ($job) => in_array($job->keyword, $this->selectedKeywords));
        }

        if ($this->selectedSources !== []) {
            $jobs = $jobs->filter(fn ($job) => in_array($job->source, $this->selectedSources));
        }

        if (trim($this->search) !== '') {
            $needle = strtolower(trim($this->search));

            $jobs = $jobs->filter(fn ($job) => str_contains(strtolower(($job->title ?? '').' '.($job->company_name ?? '').' '.$job->getLocation()), $needle));
        }

        return match ($this->sort) {
            'az' => $jobs->sortBy(fn ($job) => strtolower($job->title))->values(),
            'score' => $jobs->sortByDesc(fn ($job) => $job->rating?->status === 'completed' ? $job->rating->skills_match : -1)->values(),
            default => $jobs->sortByDesc('published_at')->values(),
        };
    }

    public function companyInitials(string $name): string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function formatDate(string $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }

    public function formatDistance(float $distance): string
    {
        return round($distance).' '.__('km');
    }

    public function aiScore($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();
        
        if (!$post) {
            return null;
        }

        if ($post->rating) {
            $post->rating->delete();
        }

        $post->rating()->create([
            'user_id' => $user->id,
            'job_id' => $post->source_id,
            'source' => $post->source,
            'job_title' => $post->title,
            'job_url' => $post->canonical_url,
        ]);

        ProcessJobRating::dispatch($post, $user);

        $this->dispatch('toast',
            message: __('AI score is being calculated. Please check back in a few moments.'),
            type: 'success'
        );
    }
};
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Job Listings') }}</h1>
                <div class="flex items-center gap-2">
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ number_format(count($jobs)) }} {{ __('jobs found') }}</p>
                    <a class="btn btn-xs mt-2" href="{{ route('profile') }}" wire:navigate>
                        {{ __('Change keywords or location') }}
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn btn-sm btn-outline gap-2" wire:click="refreshJobs" wire:loading.attr="disabled" wire:target="refreshJobs">
                    {{ __('Refresh') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 animate-spin" wire:loading wire:target="refreshJobs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
                        <polyline points="21 3 21 9 15 9"/>
                    </svg>
                </button>
                <div class="badge badge-primary badge-outline">
                    <span class="w-2 h-2 bg-primary rounded-full animate-pulse mr-2"></span>
                    {{ __('Live results') }}
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4"/>
                            <path d="M12 8h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-base-content">{{ __('AI Ratings Guide') }}</p>
                        <p class="text-sm text-base-content/60">{{ __('Scores compare your CV with each job posting. Click "AI Score" on a job to start the analysis. Tooltips on the badges explain the reasoning.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-success" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Skills Match') }}</div>
                            <div class="text-base-content/60">{{ __('80%+ = strong alignment') }}</div>
                        </div>
                        <span class="badge badge-success badge-xs">{{ __('80%+') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-warning" viewBox="0 0 24 24">
                            <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.2 0-2 .8-2 2v11c0 1.2.8 2 2 2h16c1.2 0 2-.8 2-2V9c0-1.2-.8-2-2-2zM10 4h4v3h-4V4z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Experience Relevance') }}</div>
                            <div class="text-base-content/60">{{ __('50-79% = partial match') }}</div>
                        </div>
                        <span class="badge badge-warning badge-xs">{{ __('50-79%') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-error" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Seniority Fit') }}</div>
                            <div class="text-base-content/60">{{ __('Under 50% = mismatch') }}</div>
                        </div>
                        <span class="badge badge-error badge-xs">{{ __('<50%') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-info" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Keyword Match (ATS)') }}</div>
                            <div class="text-base-content/60">{{ __('Checks required terms') }}</div>
                        </div>
                        <span class="badge badge-info badge-xs">{{ __('ATS') }}</span>
                    </div>
                </div>
                <div class="text-xs text-base-content/60">
                    {{ __('New scores appear as "Calculating..." and update automatically. Re-run "AI Score" if you update your CV or keywords.') }}
                </div>
            </div>
        </div>

        @if(count($jobs) === 0)
            <div class="card bg-base-100 shadow-sm mt-4">
                <div class="card-body items-center text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-base-content/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">{{ __('No jobs found') }}</h3>
                    <p class="text-base-content/60">{{ __('Try updating your search keywords in your profile.') }}</p>
                    <a href="{{ route('profile') }}" class="btn btn-primary mt-4">{{ __('Update Profile') }}</a>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-sm mb-4">
                <div class="card-body p-4 flex flex-col gap-3">
                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                        <label class="input input-sm flex items-center gap-2 grow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-base-content/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="{{ __('Search title, company or location...') }}"
                                class="grow"
                            />
                        </label>
                        <div class="join">
                            @foreach(['date' => __('Date'), 'az' => __('A-Z'), 'score' => __('AI score')] as $sortKey => $sortLabel)
                                <button
                                    class="btn btn-sm join-item {{ $sort === $sortKey ? 'btn-primary' : 'btn-ghost' }}"
                                    wire:click="$set('sort', '{{ $sortKey }}')"
                                >
                                    {{ $sortLabel }}
                                </button>
                            @endforeach
                        </div>
                        <button
                            class="btn btn-sm {{ $showMap ? 'btn-primary' : 'btn-outline' }} gap-2"
                            wire:click="toggleMap"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ __('Map') }}
                        </button>
                        @if(trim($search) !== '' || $selectedKeywords !== [] || $selectedSources !== [])
                            <span class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ number_format(count($this->sortedJobs)) }} / {{ number_format(count($jobs)) }} {{ __('shown') }}
                            </span>
                        @endif
                    </div>
                    @if($this->keywords->count() > 1)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-base-content/60">{{ __('Keyword') }}:</span>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    class="btn btn-xs {{ $selectedKeywords === [] ? 'btn-primary' : 'btn-ghost' }}"
                                    wire:click="$set('selectedKeywords', [])"
                                >
                                    {{ __('All') }}
                                </button>
                                @foreach($this->keywords as $kw)
                                    <button
                                        class="btn btn-xs {{ in_array($kw, $selectedKeywords) ? 'btn-primary' : 'btn-ghost' }}"
                                        wire:click="toggleKeyword('{{ $kw }}')"
                                    >
                                        {{ Str::title($kw) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($this->sources->count() > 1)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-base-content/60">{{ __('Source') }}:</span>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    class="btn btn-xs {{ $selectedSources === [] ? 'btn-primary' : 'btn-ghost' }}"
                                    wire:click="$set('selectedSources', [])"
                                >
                                    {{ __('All') }}
                                </button>
                                @foreach($this->sources as $src)
                                    <button
                                        class="btn btn-xs {{ in_array($src, $selectedSources) ? 'btn-primary' : 'btn-ghost' }}"
                                        wire:click="toggleSource('{{ $src }}')"
                                    >
                                        {{ Str::title($src) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($showMap)
                <div class="card bg-base-100 shadow-sm mb-4" wire:key="jobs-map-{{ md5($this->mapPoints->toJson()) }}">
                    <div class="card-body p-4 gap-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-base-content">{{ __('Job locations') }}</p>
                            <span class="text-xs text-base-content/60">
                                {{ number_format($this->mapPoints->count()) }} / {{ number_format(count($this->sortedJobs)) }} {{ __('jobs have a known location') }}
                            </span>
                        </div>
                        @if($this->mapPoints->isEmpty())
                            <p class="text-sm text-base-content/60">{{ __('None of the listed jobs have a known location.') }}</p>
                        @else
                            <script type="application/json" id="jobs-map-data">@json($this->mapPoints)</script>
                            <div id="jobs-map" class="h-120 rounded-box overflow-hidden z-0"></div>
                        @endif
                    </div>
                </div>
            @endif

            @if(count($this->sortedJobs) === 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body items-center text-center py-10">
                        <p class="text-base-content/60">{{ __('No jobs match your search or filters.') }}</p>
                    </div>
                </div>
            @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($this->sortedJobs as $job)
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                                    {{ $this->companyInitials($job->company_name) }}
                                </div>
                                <div class="grow min-w-0">
                                    <a href="{{ $job->canonical_url }}" target="_blank" class="font-semibold text-base hover:text-primary transition-colors line-clamp-2">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-sm text-base-content/60 mt-1">{{ $job->company_name }}</div>
                                </div>
                                @if ($job->rating?->status === 'pending')
                                    <div class="badge badge-info badge-sm" wire:poll.5000ms>
                                        {{ __('Calculating...') }}
                                    </div>
                                @elseif ($job->rating?->status === 'failed')
                                    <div class="badge badge-error badge-sm">
                                        {{ __('Failed') }}
                                    </div>
                                @elseif($job->rating?->status === 'completed')
                                    <div class="flex items-center gap-1 shrink-0">
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->skills_match_reasoning }}">
                                            <div class="badge {{ $job->rating?->skills_match >= 80 ? 'badge-success' : ($job->rating?->skills_match >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                {{ number_format($job->rating?->skills_match, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->experience_relevance_reasoning }}">
                                            <div class="badge {{ $job->rating?->experience_relevance >= 80 ? 'badge-success' : ($job->rating?->experience_relevance >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.2 0-2 .8-2 2v11c0 1.2.8 2 2 2h16c1.2 0 2-.8 2-2V9c0-1.2-.8-2-2-2zM10 4h4v3h-4V4z"/>
                                                </svg>
                                                {{ number_format($job->rating?->experience_relevance, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->seniority_fit_reasoning }}">
                                            <div class="badge {{ $job->rating?->seniority_fit >= 80 ? 'badge-success' : ($job->rating?->seniority_fit >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                                </svg>
                                                {{ number_format($job->rating?->seniority_fit, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->keyword_match_reasoning }}">
                                            <div class="badge {{ $job->rating?->keyword_match >= 80 ? 'badge-success' : ($job->rating?->keyword_match >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-14c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                </svg>
                                                {{ number_format($job->rating?->keyword_match, 0) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                                <span class="badge badge-ghost badge-sm">{{ Str::title($job['source'] ?? 'Jobindex') }}</span>
                                @if($job->keyword)
                                    <span class="badge badge-outline badge-sm" title="{{ __('Search keyword') }}">{{ Str::title($job->keyword) }}</span>
                                @endif
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $job->getLocation() }}
                                    @if(! $this->hasMapPoint($job))
                                        <span class="badge badge-warning badge-xs" title="{{ __('This job has no known coordinates, so it is not shown on the map') }}">{{ __('Not on map') }}</span>
                                    @endif
                                    @if(false)
                                        <span class="text-base-content/40">({{ $distance }})</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $job->published_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                <button class="btn btn-secondary btn-sm gap-2" wire:click="aiScore('{{ $job->id }}')">
                                    {{ __('AI Score') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                                <a href="{{ $job->canonical_url }}" target="_blank" class="btn btn-primary btn-sm gap-2">
                                    {{ __('View Job') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>

    <script>
        (function () {
            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str == null ? '' : String(str);
                return div.innerHTML;
            }

            function ensureLeaflet(callback) {
                if (window.L) {
                    return callback();
                }

                if (! window.jobsMapLeafletLoading) {
                    window.jobsMapLeafletLoading = new Promise(function (resolve) {
                        var link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(link);

                        var script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.onload = function () {
                            // Cluster + spiderfy markers that share the same city coordinates.
                            var clusterCss = document.createElement('link');
                            clusterCss.rel = 'stylesheet';
                            clusterCss.href = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css';
                            document.head.appendChild(clusterCss);

                            var clusterThemeCss = document.createElement('link');
                            clusterThemeCss.rel = 'stylesheet';
                            clusterThemeCss.href = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css';
                            document.head.appendChild(clusterThemeCss);

                            // The default clusters are pale green with white text — override
                            // with a dark circle and bolder text so the count is readable.
                            var clusterStyle = document.createElement('style');
                            clusterStyle.textContent =
                                '.marker-cluster-small,' +
                                '.marker-cluster-medium,' +
                                '.marker-cluster-large {' +
                                    'background-color: rgba(37, 99, 235, 0.25) !important;' +
                                '}' +
                                '.marker-cluster div {' +
                                    'background-color: #1d4ed8 !important;' +
                                    'color: #fff !important;' +
                                    'font-weight: 800 !important;' +
                                    'font-size: 15px !important;' +
                                    'text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45) !important;' +
                                '}' +
                                '.marker-cluster span {' +
                                    'line-height: 30px !important;' +
                                '}';
                            document.head.appendChild(clusterStyle);

                            var clusterScript = document.createElement('script');
                            clusterScript.src = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
                            clusterScript.onload = resolve;
                            document.head.appendChild(clusterScript);
                        };
                        document.head.appendChild(script);
                    });
                }

                window.jobsMapLeafletLoading.then(callback);
            }

            function initJobsMap(container, points) {
                ensureLeaflet(function () {
                    if (window.jobsMap) {
                        window.jobsMap.remove();
                        window.jobsMap = null;
                    }

                    var map = L.map(container);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    var cluster = L.markerClusterGroup();

                    points.forEach(function (point) {
                        var marker = L.marker([point.lat, point.lng])
                            .bindPopup(
                                '<div style="min-width:200px">' +
                                    '<a href="' + escapeHtml(point.url) + '" target="_blank" rel="noopener" style="font-weight:600">' + escapeHtml(point.title) + '</a>' +
                                    '<div style="font-size:12px;margin-top:2px">' + escapeHtml(point.company) + '</div>' +
                                    '<div style="font-size:12px;opacity:.65">' + escapeHtml(point.location) + '</div>' +
                                '</div>'
                            );

                        cluster.addLayer(marker);
                    });

                    map.addLayer(cluster);

                    if (points.length > 1) {
                        map.fitBounds(cluster.getBounds().pad(0.15));
                    } else {
                        map.setView([points[0].lat, points[0].lng], 12);
                    }

                    window.jobsMap = map;
                });
            }

            var mapObserver = new MutationObserver(function () {
                var container = document.getElementById('jobs-map');

                if (! container || container.dataset.initialized) {
                    return;
                }

                var data = document.getElementById('jobs-map-data');

                if (! data) {
                    return;
                }

                container.dataset.initialized = '1';
                initJobsMap(container, JSON.parse(data.textContent));
            });

            mapObserver.observe(document.body, { childList: true, subtree: true });
        })();
    </script>
</div>
