<?php

use App\Jobs\GenerateCoverLetter;
use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use App\Models\Post;
use App\Models\PostSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    // https://www.jobindex.dk/api/jobsearch/v3/jobcount?subid=1&radius=60&address=Svinglen+24%2C+8800+Viborg&q=php
    // https://www.jobindex.dk/api/jobsearch/v3?q=php&radius=60&address=Svinglen+24%2C+8800+Viborg
    // https://jobnet.dk/bff/FindJob/Search?resultsPerPage=20&pageNumber=1&orderType=BestMatch&searchString=php

    public $jobs;

    #[Url('q')]
    public string $search = '';

    #[Url('s')]
    public string $sort = 'date';

    #[Url('key')]
    public array $selectedKeywords = [];

    #[Url('src')]
    public array $selectedSources = [];

    public bool $showMap = false;

    /**
     * Id of the job whose description is shown in the modal,
     * or null when the modal is closed.
     */
    public ?int $descriptionModalJobId = null;

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
        "Virum" => [55.7948, 12.4510],
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

    public function keywordColor(?string $keyword): ?string
    {
        if (! $keyword) {
            return null;
        }

        // Golden-angle hue from a stable keyword hash, so each keyword
        // gets a distinct color that never changes between reloads.
        $hue = fmod(crc32(mb_strtolower(trim($keyword))) * 137.508, 360);

        return 'hsl('.round($hue).', 70%, 45%)';
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

        // Strip a leading postal code, e.g. "2100 København Ø", "1500-1799 København V" or "DK-2100 København".
        $city = (string) preg_replace('/^(?:[a-z]{2}-)?\d{4}(-\d{4})?[\/\s]+/u', '', $city);

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

        // Otherwise find the longest city name contained in the string on word
        // boundaries, so "København Ø" -> "København" while "Helsingør" does
        // not match "Helsinge" (the character after a match must not be a letter).
        $best = null;

        foreach ($names as $name => $canonical) {
            if ($name === '' || mb_strlen($name) <= mb_strlen($best ?? '')) {
                continue;
            }

            $pos = mb_strpos($city, $name);

            if ($pos === false) {
                continue;
            }

            $end = $pos + mb_strlen($name);

            if ($pos > 0 && preg_match('/\pL/u', mb_substr($city, $pos - 1, 1))) {
                continue;
            }

            if ($end < mb_strlen($city) && preg_match('/\pL/u', mb_substr($city, $end, 1))) {
                continue;
            }

            $best = $name;
        }

        return $best !== null ? $this->cordsForCitys[$names[$best]] : null;
    }

    /**
     * Latitude/longitude for a job, falling back to the city lookup like
     * the map does, or null when nothing is known.
     */
    public function mapCoordinates($job): ?array
    {
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

        return [(float) $lat, (float) $lng];
    }

    public function hasMapPoint($job): bool
    {
        return $this->mapCoordinates($job) !== null;
    }

    #[Computed]
    public function mapPoints()
    {
        return $this->sortedJobs
            ->map(function ($job) {
                $coords = $this->mapCoordinates($job);

                if ($coords === null) {
                    return null;
                }

                [$lat, $lng] = $coords;

                return [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'title' => $job->title,
                    'company' => $job->company_name,
                    'location' => $job->getLocation(),
                    'url' => $job->canonical_url,
                    'keyword' => $job->keyword,
                    'color' => $this->keywordColor($job->keyword),
                ];
            })
            ->filter()
            ->values();
    }

    #[Computed]
    public function userPoint(): ?array
    {
        $user = auth()->user();

        if (! $user?->latitude || ! $user?->longitude) {
            return null;
        }

        return [
            'lat' => (float) $user->latitude,
            'lng' => (float) $user->longitude,
            'city' => $user->city,
            'maxDistance' => (float) ($user->max_distance ?? 50),
        ];
    }

    /**
     * Distance in km between the user and a job, or null when either
     * has no coordinates (job coordinates fall back to the city lookup,
     * like the map does).
     */
    public function jobDistance($job): ?float
    {
        $user = $this->userPoint;

        if ($user === null) {
            return null;
        }

        $coords = $this->mapCoordinates($job);

        if ($coords === null) {
            return null;
        }

        $latFrom = deg2rad($user['lat']);
        $lngFrom = deg2rad($user['lng']);
        $latTo = deg2rad($coords[0]);
        $lngTo = deg2rad($coords[1]);

        // Haversine distance
        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
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
            'distance' => $this->userPoint === null
                ? $jobs->sortByDesc('published_at')->values()
                : $jobs->sortBy(fn ($job) => $this->jobDistance($job) ?? INF)->values(),
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

    public function generateCoverLetter($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();

        if (!$post) {
            return null;
        }

        if (!$user->defaultCoverLetter) {
            $this->dispatch('toast',
                message: __('You need to set a default cover letter on your profile first.'),
                type: 'error'
            );

            return null;
        }

        GenerateCoverLetter::dispatch($post, $user);

        $this->dispatch('toast',
            message: __('Cover letter is being generated. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function answerQuestions($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();

        if (!$post) {
            return null;
        }

        if (!$post->answerQuestions($user)) {
            $this->dispatch('toast',
                message: __('You need to define AI questions on your profile first.'),
                type: 'error'
            );

            return null;
        }

        $this->dispatch('toast',
            message: __('Your AI questions are being answered. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function getDescription($jobId, $force = false)
    {
        $job = Post::find($jobId);

        if ($job?->fetchDescription($force)) {
            $this->dispatch('toast', message: __('Job description fetched.'), type: 'success');
        } else {
            $this->dispatch('toast', message: __('Could not fetch the job description.'), type: 'error');
        }
    }

    public function viewDescription($jobId)
    {
        $job = Post::find($jobId);

        if (! $job) {
            $this->dispatch('toast', message: __('Job not found.'), type: 'error');

            return;
        }

        if (! $job->description && ! $job->fetchDescription()) {
            $this->dispatch('toast', message: __('Could not fetch the job description.'), type: 'error');

            return;
        }

        $this->descriptionModalJobId = $job->id;
    }

    public function closeDescriptionModal(): void
    {
        $this->descriptionModalJobId = null;
    }
};
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto">
        <style>
            /* Job ad description rendered from stored HTML. */
            .job-desc p { margin: 0 0 .5rem; }
            .job-desc ul { list-style: disc; padding-left: 1.25rem; margin: 0 0 .5rem; }
            .job-desc li p { margin: 0; }
            .job-desc strong { font-weight: 600; }
        </style>
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
                            @foreach(['date' => __('Date'), 'az' => __('A-Z'), 'score' => __('AI score'), 'distance' => __('Distance')] as $sortKey => $sortLabel)
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
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $this->keywordColor($kw) }}" title="{{ __('Map color') }}"></span>
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
                <style>
                    /* Fullscreen map: the card covers the viewport, the map fills
                       it and a sidebar on the side lists only the jobs in view. */
                    #jobs-map-card.is-fullscreen {
                        position: fixed;
                        inset: 0;
                        z-index: 999;
                        margin: 0;
                        border-radius: 0;
                        display: flex;
                        flex-direction: column;
                    }
                    #jobs-map-card.is-fullscreen .card-body {
                        flex: 1 1 auto;
                        min-height: 0;
                    }
                    #jobs-map-card.is-fullscreen .jobs-map-header-text,
                    #jobs-map-card.is-fullscreen .jobs-map-legend {
                        display: none;
                    }
                    /* Filter buttons live in the header next to the close
                       button, but only while the map is fullscreen. */
                    #jobs-map-card .jobs-map-filters {
                        display: none;
                    }
                    #jobs-map-card.is-fullscreen .jobs-map-filters {
                        display: flex;
                    }
                    #jobs-map-card #jobs-map-exit {
                        display: none;
                    }
                    #jobs-map-card.is-fullscreen #jobs-map-exit {
                        display: inline-flex;
                    }
                    #jobs-map-card.is-fullscreen #jobs-map-expand {
                        display: none;
                    }
                    #jobs-map-card.is-fullscreen .jobs-map-layout {
                        display: flex;
                        flex: 1 1 auto;
                        gap: 0.75rem;
                        min-height: 0;
                    }
                    #jobs-map-card.is-fullscreen #jobs-map {
                        flex: 1 1 auto;
                        height: auto;
                        min-height: 0;
                        min-width: 0;
                    }
                    #jobs-map-card.is-fullscreen #jobs-map-sidebar {
                        display: flex;
                        flex-direction: column;
                        flex: 0 0 auto;
                        width: min(20rem, 45vw);
                        min-height: 0;
                        overflow-y: auto;
                    }
                    /* Hovering or selecting a marker on the map highlights the
                       matching sidebar item(s) — several jobs can share a marker
                       when they are in the same city. */
                    #jobs-map-card .jobs-map-sidebar-item.is-highlighted,
                    #jobs-map-card .jobs-map-sidebar-item.is-selected {
                        border-color: #1d4ed8;
                        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.3);
                    }
                    #jobs-map-card .jobs-map-sidebar-item.is-selected {
                        background: rgba(29, 78, 216, 0.08);
                    }
                    body.jobs-map-fullscreen-open {
                        overflow: hidden;
                    }
                </style>
                <div class="card bg-base-100 shadow-sm mb-4" id="jobs-map-card" wire:key="jobs-map-{{ md5($this->mapPoints->sortBy(fn ($p) => $p['lat'].','.$p['lng'].','.$p['title'])->values()->toJson()) }}">
                    <div class="card-body p-4 gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="jobs-map-filters flex-wrap items-center gap-x-3 gap-y-1 min-w-0">
                                @if($this->keywords->count() > 1)
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="text-xs text-base-content/60">{{ __('Keyword') }}:</span>
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
                                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $this->keywordColor($kw) }}" title="{{ __('Map color') }}"></span>
                                                {{ Str::title($kw) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @if($this->sources->count() > 1)
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="text-xs text-base-content/60">{{ __('Source') }}:</span>
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
                                @endif
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="text-xs text-base-content/60">{{ __('Sort') }}:</span>
                                    <div class="join">
                                        @foreach(['date' => __('Date'), 'az' => __('A-Z'), 'score' => __('AI score'), 'distance' => __('Distance')] as $sortKey => $sortLabel)
                                            <button
                                                class="btn btn-xs join-item {{ $sort === $sortKey ? 'btn-primary' : 'btn-ghost' }}"
                                                wire:click="$set('sort', '{{ $sortKey }}')"
                                            >
                                                {{ $sortLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="text-xs text-base-content/60 whitespace-nowrap">
                                    {{ number_format($this->mapPoints->count()) }} / {{ number_format(count($jobs)) }} {{ __('shown') }}
                                </span>
                            </div>
                            <div class="jobs-map-header-text">
                                <p class="text-sm font-semibold text-base-content">{{ __('Job locations') }}</p>
                                <span class="text-xs text-base-content/60">
                                    {{ number_format($this->mapPoints->count()) }} / {{ number_format(count($this->sortedJobs)) }} {{ __('jobs have a known location') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="jobs-map-expand" class="btn btn-sm btn-ghost gap-2" title="{{ __('Expand map') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 3H5a2 2 0 0 0-2 2v3"/>
                                        <path d="M21 8V5a2 2 0 0 0-2-2h-3"/>
                                        <path d="M3 16v3a2 2 0 0 0 2 2h3"/>
                                        <path d="M16 21h3a2 2 0 0 0 2-2v-3"/>
                                    </svg>
                                    {{ __('Expand') }}
                                </button>
                                <button id="jobs-map-exit" class="btn btn-sm btn-circle btn-ghost" title="{{ __('Close map') }}" aria-label="{{ __('Close map') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @if($this->mapPoints->isEmpty())
                            <p class="text-sm text-base-content/60">{{ __('None of the listed jobs have a known location.') }}</p>
                        @else
                            @if($this->mapPoints->pluck('keyword')->filter()->unique()->count() > 1)
                                <div class="jobs-map-legend flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-base-content/60">{{ __('Keyword colors') }}:</span>
                                    @foreach($this->mapPoints->pluck('keyword')->filter()->unique()->sort() as $kw)
                                        <span class="badge badge-ghost badge-sm gap-1.5">
                                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $this->keywordColor($kw) }}"></span>
                                            {{ Str::title($kw) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="jobs-map-layout">
                                <script type="application/json" id="jobs-map-data">@json($this->mapPoints)</script>
                                <script type="application/json" id="jobs-map-user-data">@json($this->userPoint)</script>
                                <div id="jobs-map" class="h-120 rounded-box overflow-hidden z-0"></div>
                                <div id="jobs-map-sidebar" class="hidden border-l border-base-300 flex-col pl-3">
                                    <div class="sticky top-0 z-10 bg-base-100 py-1 pr-1 shrink-0">
                                        <span class="text-xs text-base-content/60">
                                            {{ __('In view') }}: <span id="jobs-map-sidebar-count">0</span>
                                        </span>
                                    </div>
                                    <div id="jobs-map-sidebar-list" class="flex flex-col gap-2">
                                        @foreach($this->sortedJobs as $job)
                                            @php
                                            $coords = $this->mapCoordinates($job)
                                            @endphp
                                            @if($coords !== null)
                                                <a
                                                    href="{{ $job->canonical_url }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="jobs-map-sidebar-item card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-100 shrink-0"
                                                    data-map-lat="{{ $coords[0] }}"
                                                    data-map-lng="{{ $coords[1] }}"
                                                    data-map-color="{{ $this->keywordColor($job->keyword) }}"
                                                >
                                                    <div class="card-body p-3 gap-1">
                                                        <div class="flex items-start gap-2">
                                                            @if($job->keyword)
                                                                <span class="w-2.5 h-2.5 rounded-full shrink-0 mt-1.5" style="background: {{ $this->keywordColor($job->keyword) }}" title="{{ __('Map color') }}"></span>
                                                            @endif
                                                            <span class="font-medium text-sm hover:text-primary transition-colors line-clamp-2">{{ $job->title }}</span>
                                                        </div>
                                                        <div class="text-xs text-base-content/60">{{ $job->company_name }}</div>
                                                        <div class="text-xs text-base-content/60 flex flex-wrap items-center gap-x-1.5">
                                                            <span>{{ $job->getLocation() }}</span>
                                                            @if($this->jobDistance($job) !== null)
                                                                <span class="text-base-content/40">({{ $this->formatDistance($this->jobDistance($job)) }})</span>
                                                            @endif
                                                            <span class="text-base-content/40">·</span>
                                                            <span>{{ $job->published_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                    <p id="jobs-map-sidebar-empty" class="hidden text-sm text-base-content/60 text-center py-6 m-0">
                                        {{ __('No jobs in this area. Pan or zoom the map.') }}
                                    </p>
                                </div>
                            </div>
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
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-100">
                        <div class="card-body p-5 h-full">
                            <div class="flex items-start gap-4">
                                <div class="relative w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0 overflow-hidden">
                                    {{ $this->companyInitials($job->company_name) }}
                                    @if($job->company_logo_url)
                                        <img src="{{ $job->company_logo_url }}" alt="{{ $job->company_name }}" class="absolute inset-0 w-full h-full object-contain bg-base-100" loading="lazy" referrerpolicy="no-referrer" onerror="this.remove()">
                                    @endif
                                </div>
                                <div class="grow min-w-0">
                                    <a href="{{ $job->canonical_url }}" target="_blank" class="font-semibold text-base hover:text-primary transition-colors line-clamp-2">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-sm text-base-content/60 mt-1">{{ $job->company_name }}</div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                                <span class="badge badge-ghost badge-sm">{{ Str::title($job['source'] ?? 'Jobindex') }}</span>
                                @if($job->keyword)
                                    <span class="badge badge-outline badge-sm gap-1.5" title="{{ __('Search keyword') }}"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $this->keywordColor($job->keyword) }}" title="{{ __('Map color') }}"></span>{{ Str::title($job->keyword) }}</span>
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
                                    @if($this->jobDistance($job) !== null)
                                        <span class="text-base-content/40">({{ $this->formatDistance($this->jobDistance($job)) }})</span>
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
                            @if ($job->rating || $job->questionAnswers)
                            <div class="flex flex-col lg:flex-row gap-2 mt-3">
                                @if ($job->rating)
                                <div class="flex-1 min-w-0 rounded-box bg-base-200/50 border border-base-300 px-3 py-2" @if ($job->rating->status === 'pending') wire:poll.5000ms @endif>
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-base-content/50 mb-1.5">{{ __('AI Score') }}</div>
                                    @if ($job->rating->status === 'pending')
                                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                                            <span class="loading loading-spinner loading-xs"></span>
                                            {{ __('Calculating...') }}
                                        </div>
                                    @elseif ($job->rating->status === 'failed')
                                        <span class="badge badge-error badge-sm">{{ __('Failed') }}</span>
                                    @else
                                        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                                            @foreach(['skills_match' => __('Skills'), 'experience_relevance' => __('Experience'), 'seniority_fit' => __('Seniority'), 'keyword_match' => __('Keywords')] as $field => $label)
                                            <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->{$field.'_reasoning'} }}">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs text-base-content/60">{{ $label }}</span>
                                                    <div class="badge {{ $job->rating?->{$field} >= 80 ? 'badge-success' : ($job->rating?->{$field} >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm">
                                                        {{ number_format($job->rating?->{$field}, 0) }}%
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @endif

                                @if ($job->questionAnswers)
                                <div class="flex-1 min-w-0 rounded-box bg-base-200/50 border border-base-300 px-3 py-2" @if ($job->questionAnswers->status === 'pending') wire:poll.5000ms @endif>
                                    <div class="text-[10px] font-semibold uppercase tracking-wide text-base-content/50 mb-1.5">{{ __('Your Questions') }}</div>
                                    @if ($job->questionAnswers->status === 'pending')
                                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                                            <span class="loading loading-spinner loading-xs"></span>
                                            {{ __('Answering...') }}
                                        </div>
                                    @elseif ($job->questionAnswers->status === 'failed')
                                        <span class="badge badge-error badge-sm">{{ __('Answers failed') }}</span>
                                    @else
                                        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                                            @foreach(auth()->user()->questions ?? [] as $definition)
                                            @php
                                                $saved = $job->questionAnswers->answers[$definition['key']] ?? null;
                                            @endphp
                                            @if($saved)
                                            <div class="tooltip tooltip-info" data-tip="{{ $definition['question'] }}">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs text-base-content/60 truncate">{{ Str::title(str_replace('_', ' ', $definition['key'])) }}</span>
                                                    @if($saved['type'] === 'boolean')
                                                    <div class="badge {{ ($saved['answer']['probability'] ?? 0) >= 0.5 ? 'badge-success' : 'badge-error' }} badge-sm">
                                                        {{ ($saved['answer']['probability'] ?? 0) >= 0.5 ? __('Yes') : __('No') }}
                                                    </div>
                                                    @elseif($saved['type'] === 'choice')
                                                    <div class="badge badge-primary badge-sm">
                                                        {{ $saved['answer']['choice'] ?? '—' }}
                                                    </div>
                                                    @else
                                                    @php
                                                        $pct = round(($saved['answer']['score'] ?? 0) * 100 / max(count($definition['levels'] ?? []) - 1, 1));
                                                    @endphp
                                                    <div class="badge {{ $pct >= 80 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm">
                                                        {{ $pct }}%
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-2 mt-auto pt-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button
                                        class="btn btn-ghost btn-xs gap-1.5"
                                        wire:click="aiScore('{{ $job->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="aiScore('{{ $job->id }}')"
                                        title="{{ __('Compare this job with your CV') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        {{ __('AI Score') }}
                                    </button>
                                    @if(auth()->user()?->questions)
                                    <button
                                        class="btn btn-ghost btn-xs gap-1.5"
                                        wire:click="answerQuestions('{{ $job->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="answerQuestions('{{ $job->id }}')"
                                        title="{{ __('Answer your AI questions about this job') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                        {{ __('Questions') }}
                                    </button>
                                    @endif
                                    @if(auth()->user()?->defaultCoverLetter)
                                    <button
                                        class="btn btn-ghost btn-xs gap-1.5"
                                        wire:click="generateCoverLetter('{{ $job->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="generateCoverLetter('{{ $job->id }}')"
                                        title="{{ __('Generate a tailored cover letter') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                            <line x1="16" y1="13" x2="8" y2="13"/>
                                            <line x1="16" y1="17" x2="8" y2="17"/>
                                            <line x1="10" y1="9" x2="8" y2="9"/>
                                        </svg>
                                        {{ __('Cover Letter') }}
                                    </button>
                                    @endif
                                    @if ($job->description)
                                    <button class="btn btn-ghost btn-xs gap-1.5" wire:click="viewDescription('{{ $job->id }}')">
                                        {{ __('Description') }}
                                        <span wire:loading wire:target="viewDescription('{{ $job->id }}')" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                    @else
                                    <button class="btn btn-ghost btn-error btn-xs gap-1.5" wire:click="getDescription('{{ $job->id }}')">
                                        {{ __('Get Description') }}
                                        <span wire:loading wire:target="getDescription('{{ $job->id }}')" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                    @endif
                                </div>
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

            @php
                $descriptionJob = $descriptionModalJobId ? Post::find($descriptionModalJobId) : null;
            @endphp
            @if ($descriptionJob)
                <div class="modal modal-open" wire:keydown.escape.window="closeDescriptionModal">
                    <div class="modal-box max-w-3xl">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-base line-clamp-2">{{ $descriptionJob->title }}</h3>
                                <p class="text-sm opacity-60 mt-0.5">{{ $descriptionJob->company_name }} · {{ $descriptionJob->getLocation() }}</p>
                            </div>
                            <form method="dialog" class="modal-action mt-0">
                                <button type="button" class="btn btn-circle btn-ghost btn-sm" wire:click="closeDescriptionModal" aria-label="{{ __('Close') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <div class="job-desc mt-4 max-h-[60vh] overflow-y-auto rounded-lg border border-base-300 bg-base-200/50 p-4 text-sm leading-relaxed">
                            {!! $descriptionJob->description !!}
                        </div>
                        <div class="modal-action">
                            <button type="button" class="btn btn-warning btn-sm" wire:click="getDescription('{{ $descriptionJob->id }}', true)">
                                {{ __('Reload Description') }}
                                <span wire:loading wire:target="getDescription('{{ $descriptionJob->id }}', true)" class="loading loading-spinner loading-xs"></span>
                            </button>
                            <a href="{{ $descriptionJob->canonical_url }}" target="_blank" class="btn btn-primary btn-sm">{{ __('View Job') }}</a>
                        </div>
                    </div>
                    <div class="modal-backdrop bg-black/60" wire:click="closeDescriptionModal"></div>
                </div>
            @endif
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
                            // The cluster icon is built from scratch via iconCreateFunction
                            // below, so the plugin's default stylesheets aren't loaded —
                            // everything it needs lives in this single style block.
                            var clusterStyle = document.createElement('style');
                            clusterStyle.textContent =
                                // Cluster bubbles: same dot design as the job markers,
                                // scaled up with the job count inside.
                                '.job-cluster-icon { background: none; border: none; }' +
                                '.job-cluster {' +
                                    'border-radius: 50%;' +
                                    'background: #1d4ed8;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.3);' +
                                    'color: #fff;' +
                                    'font-weight: 800;' +
                                    'font-size: 13px;' +
                                    'text-align: center;' +
                                    'text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);' +
                                    'transition: transform 0.15s ease;' +
                                '}' +
                                '.job-cluster-icon:hover .job-cluster {' +
                                    'transform: scale(1.1);' +
                                '}' +
                                // Distinct pulsing dot for the user's own location,
                                // so it stands apart from the blue job markers.
                                '.user-location-icon { background: none; border: none; }' +
                                '.user-location-dot {' +
                                    'width: 16px;' +
                                    'height: 16px;' +
                                    'border-radius: 50%;' +
                                    'background: #16a34a;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.4), 0 2px 6px rgba(0, 0, 0, 0.3);' +
                                    'animation: user-location-pulse 2s infinite;' +
                                '}' +
                                '@keyframes user-location-pulse {' +
                                    '0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.5), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                    '70% { box-shadow: 0 0 0 14px rgba(22, 163, 74, 0), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                    '100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0), 0 2px 6px rgba(0, 0, 0, 0.3); }' +
                                '}' +
                                // Job markers: smaller blue dots in the same family as the
                                // cluster color, with a hover scale so they feel interactive.
                                '.job-marker-icon { background: none; border: none; }' +
                                '.job-marker-dot {' +
                                    'width: 16px;' +
                                    'height: 16px;' +
                                    'border-radius: 50%;' +
                                    'background: #1d4ed8;' +
                                    'border: 3px solid #fff;' +
                                    'box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2), 0 1px 4px rgba(0, 0, 0, 0.3);' +
                                    'transition: transform 0.15s ease;' +
                                '}' +
                                '.job-marker-icon:hover .job-marker-dot {' +
                                    'transform: scale(1.4);' +
                                '}' +
                                // Hovering a job in the fullscreen sidebar pings its
                                // position on the map with a pulsing ring, and scales
                                // up the dot — or the cluster bubble that currently
                                // represents it — while the hover lasts.
                                '.jobs-map-highlight-icon { background: none; border: none; pointer-events: none; }' +
                                '.jobs-map-highlight-ring {' +
                                    'width: 36px;' +
                                    'height: 36px;' +
                                    'border-radius: 50%;' +
                                    'border: 3px solid #1d4ed8;' +
                                    'box-sizing: border-box;' +
                                    'animation: jobs-map-highlight-pulse 1.2s ease-out infinite;' +
                                '}' +
                                '@keyframes jobs-map-highlight-pulse {' +
                                    '0% { transform: scale(0.4); opacity: 1; }' +
                                    '100% { transform: scale(1.2); opacity: 0; }' +
                                '}' +
                                '.job-marker-dot.is-active {' +
                                    'transform: scale(1.9);' +
                                    'box-shadow: 0 0 0 5px rgba(29, 78, 216, 0.35), 0 1px 4px rgba(0, 0, 0, 0.3);' +
                                '}' +
                                '.job-cluster.is-active {' +
                                    'transform: scale(1.15);' +
                                    'box-shadow: 0 0 0 5px rgba(29, 78, 216, 0.35), 0 2px 8px rgba(0, 0, 0, 0.3);' +
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

            // Fullscreen state lives on window (not Livewire) so expanding is
            // instant with no server round trip. Livewire morphs can strip the
            // runtime classes, so re-apply them after every commit and every
            // map re-init (jobsMapSetFullscreen is idempotent).
            window.jobsMapExpanded = false;

            function jobsMapFilterSidebar(map) {
                var list = document.getElementById('jobs-map-sidebar-list');

                if (! list) {
                    return;
                }

                var bounds = map.getBounds();
                var count = 0;

                list.querySelectorAll('.jobs-map-sidebar-item').forEach(function (item) {
                    var lat = parseFloat(item.getAttribute('data-map-lat'));
                    var lng = parseFloat(item.getAttribute('data-map-lng'));
                    var inView = bounds.contains([lat, lng]);

                    item.classList.toggle('hidden', ! inView);

                    if (inView) {
                        count++;
                    }
                });

                var countEl = document.getElementById('jobs-map-sidebar-count');

                if (countEl) {
                    countEl.textContent = count;
                }

                var empty = document.getElementById('jobs-map-sidebar-empty');

                if (empty) {
                    empty.classList.toggle('hidden', count > 0);
                }
            }

            function jobsMapClearJobHighlight() {
                if (window.jobsMap && window.jobsMapHighlight) {
                    window.jobsMap.removeLayer(window.jobsMapHighlight);
                    window.jobsMapHighlight = null;
                }

                document.querySelectorAll('.job-marker-dot.is-active, .job-cluster.is-active').forEach(function (el) {
                    el.classList.remove('is-active');
                });
            }

            /**
             * Highlight the map position for a hovered sidebar job: a pulsing
             * ring at its coordinates, plus a scaled-up dot — or the cluster
             * bubble that currently represents it when the marker is inside
             * a cluster.
             */
            function jobsMapHighlightJob(lat, lng, color) {
                jobsMapClearJobHighlight();

                var map = window.jobsMap;

                if (! map) {
                    return;
                }

                var ringIcon = L.divIcon({
                    className: 'jobs-map-highlight-icon',
                    html: '<div class="jobs-map-highlight-ring" style="border-color:' + (color || '#1d4ed8') + '"></div>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                window.jobsMapHighlight = L.marker([lat, lng], { icon: ringIcon, interactive: false, zIndexOffset: 2000 }).addTo(map);

                (window.jobsMapJobMarkers || []).forEach(function (entry) {
                    // Coordinates come from the same source on both sides, but
                    // compare with a tolerance so float formatting can't miss.
                    if (Math.abs(entry.lat - lat) > 1e-9 || Math.abs(entry.lng - lng) > 1e-9) {
                        return;
                    }

                    var visible = entry.cluster.getVisibleParent(entry.marker);

                    if (! visible) {
                        return;
                    }

                    var el = visible.getElement();

                    if (! el) {
                        return;
                    }

                    var target = el.querySelector('.job-marker-dot') || el.querySelector('.job-cluster');

                    if (target) {
                        target.classList.add('is-active');
                    }
                });
            }

            /**
             * Sidebar items that correspond to the given map markers. Markers
             * match by coordinates (same source on both sides, compared with
             * a tolerance), so one marker matches every job in the same spot.
             */
            function jobsMapSidebarItemsForMarkers(markers) {
                var list = document.getElementById('jobs-map-sidebar-list');

                if (! list) {
                    return [];
                }

                var entries = (window.jobsMapJobMarkers || []).filter(function (entry) {
                    return markers.indexOf(entry.marker) !== -1;
                });

                var items = [];

                if (! entries.length) {
                    return items;
                }

                list.querySelectorAll('.jobs-map-sidebar-item').forEach(function (item) {
                    var lat = parseFloat(item.getAttribute('data-map-lat'));
                    var lng = parseFloat(item.getAttribute('data-map-lng'));

                    var matches = entries.some(function (entry) {
                        return Math.abs(entry.lat - lat) < 1e-9 && Math.abs(entry.lng - lng) < 1e-9;
                    });

                    if (matches) {
                        items.push(item);
                    }
                });

                return items;
            }

            function jobsMapClearSidebarHover() {
                (window.jobsMapSidebarHoverItems || []).forEach(function (item) {
                    item.classList.remove('is-highlighted');
                });

                window.jobsMapSidebarHoverItems = [];
            }

            // Hovering a marker (or cluster) on the map highlights its sidebar
            // item(s) and scrolls the first one into view.
            function jobsMapHoverSidebar(markers) {
                jobsMapClearSidebarHover();

                if (! window.jobsMapExpanded) {
                    return;
                }

                window.jobsMapSidebarHoverItems = jobsMapSidebarItemsForMarkers(markers);

                window.jobsMapSidebarHoverItems.forEach(function (item, index) {
                    item.classList.add('is-highlighted');

                    if (index === 0) {
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                });
            }

            // Clicking a marker selects its sidebar item(s); the selection stays
            // until another marker is clicked, empty map area is clicked or
            // fullscreen is exited.
            function jobsMapSelectSidebar(markers) {
                (window.jobsMapSidebarSelectedItems || []).forEach(function (item) {
                    item.classList.remove('is-selected');
                });

                window.jobsMapSidebarSelectedItems = jobsMapSidebarItemsForMarkers(markers);

                window.jobsMapSidebarSelectedItems.forEach(function (item, index) {
                    item.classList.add('is-selected');

                    if (index === 0 && window.jobsMapExpanded) {
                        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                });
            }

            document.addEventListener('mouseover', function (e) {
                var item = e.target.closest('.jobs-map-sidebar-item');

                if (! item || item.contains(e.relatedTarget)) {
                    return;
                }

                jobsMapHighlightJob(
                    parseFloat(item.getAttribute('data-map-lat')),
                    parseFloat(item.getAttribute('data-map-lng')),
                    item.getAttribute('data-map-color')
                );
            });

            document.addEventListener('mouseout', function (e) {
                var item = e.target.closest('.jobs-map-sidebar-item');

                if (! item || item.contains(e.relatedTarget)) {
                    return;
                }

                jobsMapClearJobHighlight();
            });

            function jobsMapSetFullscreen(active) {
                window.jobsMapExpanded = active;

                if (! active) {
                    jobsMapClearJobHighlight();
                    jobsMapClearSidebarHover();
                    jobsMapSelectSidebar([]);
                }

                var card = document.getElementById('jobs-map-card');

                document.body.classList.toggle('jobs-map-fullscreen-open', active);

                if (card) {
                    card.classList.toggle('is-fullscreen', active);
                }

                // Leaflet must re-measure after the layout change, and the
                // sidebar needs an initial filter for the current viewport.
                requestAnimationFrame(function () {
                    if (window.jobsMap) {
                        try {
                            window.jobsMap.invalidateSize();
                        } catch (err) {
                            // The map may have been removed by a concurrent re-init.
                        }

                        if (active) {
                            jobsMapFilterSidebar(window.jobsMap);
                        }
                    }
                });
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('#jobs-map-expand')) {
                    jobsMapSetFullscreen(true);
                }

                if (e.target.closest('#jobs-map-exit')) {
                    jobsMapSetFullscreen(false);
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && window.jobsMapExpanded) {
                    jobsMapSetFullscreen(false);
                }
            });

            document.addEventListener('livewire:init', function () {
                Livewire.hook('commit', function ({ succeed }) {
                    succeed(function () {
                        if (window.jobsMapExpanded) {
                            requestAnimationFrame(function () {
                                jobsMapSetFullscreen(true);
                            });
                        }
                    });
                });
            });

            function initJobsMap(container, points) {
                ensureLeaflet(function () {
                    // A re-render replaces the card, which loses the fullscreen
                    // classes — restore them before the map is created so it
                    // measures at full size from the start.
                    if (window.jobsMapExpanded) {
                        var freshCard = container.closest('#jobs-map-card');

                        if (freshCard) {
                            freshCard.classList.add('is-fullscreen');
                        }

                        document.body.classList.add('jobs-map-fullscreen-open');
                    }

                    if (window.jobsMap) {
                        window.jobsMap.remove();
                        window.jobsMap = null;
                    }

                    // Rebuilt below from the markers, so sidebar hover can find
                    // the marker (or cluster) that represents a given location.
                    window.jobsMapJobMarkers = [];
                    window.jobsMapHighlight = null;

                    var map = L.map(container, { attributionControl: false });
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 18,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    // maxClusterRadius: 0 disables distance-based clustering entirely,
                    // so only markers sharing (nearly) the same coordinates group up —
                    // e.g. jobs in the same city — and everything else stays a single
                    // marker no matter how far the map is zoomed out.
                    var cluster = L.markerClusterGroup({
                        maxClusterRadius: 18,
                        iconCreateFunction: function (group) {
                            var count = group.getChildCount();
                            var size = count < 10 ? 36 : count < 50 ? 44 : 52;

                            // Count the markers per keyword color and split the
                            // cluster background like a pie chart: each keyword
                            // gets a share of the circle proportional to its
                            // number of jobs (2 green + 1 blue -> 2/3 green,
                            // 1/3 blue). A single keyword (or none) stays a
                            // solid color.
                            var colorCounts = {};
                            var colors = [];
                            var total = 0;

                            group.getAllChildMarkers().forEach(function (marker) {
                                var color = marker.options.keywordColor || '#1d4ed8';

                                if (colorCounts[color] === undefined) {
                                    colorCounts[color] = 0;
                                    colors.push(color);
                                }

                                colorCounts[color]++;
                                total++;
                            });

                            var background;

                            if (colors.length > 1) {
                                var stops = [];
                                var cumulative = 0;

                                colors.forEach(function (color) {
                                    var start = cumulative / total * 100;
                                    cumulative += colorCounts[color];
                                    stops.push(color + ' ' + start.toFixed(2) + '% ' + (cumulative / total * 100).toFixed(2) + '%');
                                });

                                background = 'conic-gradient(' + stops.join(', ') + ')';
                            } else {
                                background = colors[0] || '#1d4ed8';
                            }

                            return L.divIcon({
                                className: 'job-cluster-icon',
                                html: '<div class="job-cluster" style="width:' + size + 'px;height:' + size + 'px;line-height:' + (size - 6) + 'px;background:' + background + '">' + count + '</div>',
                                iconSize: L.point(size, size)
                            });
                        }
                    });

                    points.forEach(function (point) {
                        var jobIcon = L.divIcon({
                            className: 'job-marker-icon',
                            html: '<div class="job-marker-dot"' + (point.color ? ' style="background:' + point.color + '"' : '') + '></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        var marker = L.marker([point.lat, point.lng], { icon: jobIcon, keywordColor: point.color })
                            .bindPopup(
                                '<div style="min-width:200px">' +
                                    '<a href="' + escapeHtml(point.url) + '" target="_blank" rel="noopener" style="font-weight:600">' + escapeHtml(point.title) + '</a>' +
                                    '<div style="font-size:12px;margin-top:2px">' + escapeHtml(point.company) +
                                        (point.color
                                            ? ' - <span style="color:' + point.color + ';font-weight:600">' + escapeHtml(point.keyword) + '</span>'
                                            : (point.keyword ? ' - ' + escapeHtml(point.keyword) : '')) +
                                    '</div>' +
                                    '<div style="font-size:12px;opacity:.65">' + escapeHtml(point.location) + '</div>' +
                                '</div>'
                            );

                        cluster.addLayer(marker);

                        // Hover and click on the map highlight/select the job's
                        // sidebar item — the reverse of the sidebar hover ping.
                        marker.on('mouseover', function () {
                            jobsMapHoverSidebar([marker]);
                        });

                        marker.on('mouseout', jobsMapClearSidebarHover);

                        marker.on('click', function () {
                            jobsMapSelectSidebar([marker]);
                        });

                        window.jobsMapJobMarkers.push({
                            lat: point.lat,
                            lng: point.lng,
                            marker: marker,
                            cluster: cluster
                        });
                    });

                    map.addLayer(cluster);

                    // A cluster bubble represents several markers, so hover
                    // highlights every sidebar item inside it.
                    cluster.on('clustermouseover', function (e) {
                        jobsMapHoverSidebar(e.layer.getAllChildMarkers());
                    });

                    cluster.on('clustermouseout', jobsMapClearSidebarHover);

                    // The user's own location, kept out of the job cluster so it
                    // never merges with job markers and always stays on top.
                    var userMarker = null;
                    var userData = document.getElementById('jobs-map-user-data');
                    var userPoint = userData ? JSON.parse(userData.textContent) : null;

                    if (userPoint) {
                        var userIcon = L.divIcon({
                            className: 'user-location-icon',
                            html: '<div class="user-location-dot"></div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });

                        userMarker = L.marker([userPoint.lat, userPoint.lng], { icon: userIcon, zIndexOffset: 1000 })
                            .bindPopup(
                                '<div style="min-width:150px">' +
                                    '<span style="font-weight:600">' + escapeHtml(userPoint.city || 'Your location') + '</span>' +
                                    '<div style="font-size:12px;opacity:.65">Your location</div>' +
                                    (userPoint.maxDistance ? '<div style="font-size:12px;opacity:.65">' + userPoint.maxDistance + ' km search radius</div>' : '') +
                                '</div>'
                            )
                            .addTo(map);

                        // The user's job-search radius (max_distance, in km) as a
                        // dashed circle around their location.
                        if (userPoint.maxDistance) {
                            var radiusCircle = L.circle([userPoint.lat, userPoint.lng], {
                                radius: userPoint.maxDistance * 1000,
                                color: '#16a34a',
                                weight: 2,
                                opacity: 0.7,
                                dashArray: '6 8',
                                fillColor: '#16a34a',
                                fillOpacity: 0.06,
                                // Non-interactive so clicks pass through to the job
                                // markers inside the circle.
                                interactive: false
                            }).addTo(map);

                            userMarker.radiusCircle = radiusCircle;
                        }
                    }

                    var bounds = cluster.getBounds();

                    if (userMarker) {
                        bounds.extend(userMarker.getLatLng());
                    }

                    if (points.length + (userMarker ? 1 : 0) > 1) {
                        map.fitBounds(bounds.pad(0.15));
                    } else if (userMarker) {
                        map.setView([userPoint.lat, userPoint.lng], 12);
                    } else {
                        map.setView([points[0].lat, points[0].lng], 12);
                    }

                    window.jobsMap = map;

                    // Clicking empty map area expands to fullscreen. Clicks on
                    // markers, clusters, popups and controls have their own
                    // handlers and are excluded so links keep working.
                    map.on('click', function (e) {
                        var target = e.originalEvent && e.originalEvent.target;

                        if (target && target.closest && target.closest('.leaflet-popup, .leaflet-control')) {
                            return;
                        }

                        // Clicking empty map area while fullscreen clears the
                        // current selection; markers stop propagation so their
                        // clicks never reach this handler.
                        if (window.jobsMapExpanded) {
                            jobsMapSelectSidebar([]);
                        }

                        jobsMapSetFullscreen(true);
                    });

                    // Panning or zooming re-filters the sidebar to the jobs
                    // that are currently in view.
                    map.on('moveend', function () {
                        if (window.jobsMapExpanded) {
                            jobsMapFilterSidebar(map);
                        }
                    });

                    if (window.jobsMapExpanded) {
                        requestAnimationFrame(function () {
                            map.invalidateSize();
                            jobsMapFilterSidebar(map);
                        });
                    }
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
