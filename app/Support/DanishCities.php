<?php

namespace App\Support;

/**
 * Coordinates for Danish towns, used to place jobs on the map and measure
 * distances when a job has no coordinates of its own.
 *
 * Lives outside the Livewire component on purpose: as a public property this
 * ~200 entry array was serialised into every single Livewire request/response.
 */
class DanishCities
{
    /** @var array<string, array{0: float, 1: float}> */
    private const CITIES = [
        'Copenhagen' => [55.6761, 12.5689],
        'Aarhus' => [56.1564, 10.2097],
        'Odense' => [55.4000, 10.3833],
        'Aalborg' => [57.0500, 9.9167],
        'Esbjerg' => [55.4708, 8.4514],
        'Randers' => [56.4569, 10.0392],
        'Horsens' => [55.8619, 9.8519],
        'Kolding' => [55.4908, 9.4722],
        'Vejle' => [55.7083, 9.5333],
        'Roskilde' => [55.6417, 12.0808],
        'Silkeborg' => [56.1667, 9.5500],
        'Herning' => [56.1361, 8.9767],
        'Hørsholm' => [55.8786, 12.4992],
        'Helsingør' => [56.0360, 12.6106],
        'Næstved' => [55.2333, 11.7667],
        'Viborg' => [56.4500, 9.4000],
        'Fredericia' => [55.5644, 9.7597],
        'Køge' => [55.4500, 12.1833],
        'Taastrup' => [55.6500, 12.3000],
        'Holstebro' => [56.3581, 8.6175],
        'Hillerød' => [55.9333, 12.3000],
        'Slagelse' => [55.4008, 11.3500],
        'Holbæk' => [55.7181, 11.7103],
        'Sønderborg' => [54.9114, 9.7867],
        'Svendborg' => [55.0597, 10.6100],
        'Hjørring' => [57.4600, 9.9867],
        'Ringsted' => [55.4406, 11.7936],
        'Nørresundby' => [57.0583, 9.9228],
        'Frederikshavn' => [57.4339, 10.5361],
        'Haderslev' => [55.2500, 9.5000],
        'Birkerød' => [55.8474, 12.4280],
        'Farum' => [55.8083, 12.3581],
        'Skanderborg' => [56.0333, 9.9333],
        'Skive' => [56.5667, 9.0269],
        'Smørumnedre' => [55.7380, 12.3020],
        'Nyborg' => [55.3097, 10.7917],
        'Solrød Strand' => [55.5319, 12.2194],
        'Frederikssund' => [55.8406, 12.0637],
        'Ikast' => [56.1356, 9.1547],
        'Lillerød' => [55.8708, 12.3560],
        'Middelfart' => [55.5058, 9.7306],
        'Hedehusene' => [55.6500, 12.1986],
        'Kalundborg' => [55.6814, 11.0850],
        'Aabenraa' => [55.0447, 9.4195],
        'Nykøbing Falster' => [54.7667, 11.8833],
        'Korsør' => [55.3283, 11.1397],
        'Varde' => [55.6200, 8.4806],
        'Grenaa' => [56.4133, 10.8758],
        'Rønne' => [55.1000, 14.7000],
        'Odder' => [55.9725, 10.1497],
        'Thisted' => [56.9539, 8.6903],
        'Hedensted' => [55.7725, 9.7017],
        'Værløse' => [55.7828, 12.3700],
        'Brønderslev' => [57.2651, 9.9599],
        'Frederiksværk' => [55.9699, 12.0199],
        'Haslev' => [55.3269, 11.9636],
        'Hobro' => [56.6333, 9.8000],
        'Dragør' => [55.5925, 12.6722],
        'Nakskov' => [54.8333, 11.1500],
        'Vordingborg' => [55.0083, 11.9103],
        'Jyllinge' => [55.7514, 12.1039],
        'Vejen' => [55.4758, 9.1381],
        'Lystrup' => [56.2456, 10.2347],
        'Struer' => [56.4917, 8.5944],
        'Ringkøbing' => [56.0833, 8.2500],
        'Humlebæk' => [55.9600, 12.5328],
        'Helsinge' => [56.0219, 12.1994],
        'Støvring' => [56.8890, 9.8310],
        'Galten' => [56.1564, 9.9050],
        'Sæby' => [57.3327, 10.5267],
        'Fredensborg' => [55.9738, 12.4051],
        'Nykøbing Mors' => [56.7981, 8.8622],
        'Nivå' => [55.9340, 12.5048],
        'Aars' => [56.8031, 9.5128],
        'Måløv' => [55.7500, 12.3333],
        'Løgten' => [56.2786, 10.3158],
        'Hundested' => [55.9636, 11.8525],
        'Hadsten' => [56.3256, 10.0485],
        'Hørning' => [56.0850, 10.0364],
        'Hinnerup' => [56.2644, 10.0633],
        'Sorø' => [55.4366, 11.5592],
        'Ribe' => [55.3272, 8.7631],
        'Svenstrup' => [56.9711, 9.8467],
        'Skjern' => [55.9436, 8.4978],
        'Ry' => [56.0922, 9.7581],
        'Billund' => [55.7243, 9.1188],
        'Tønder' => [54.9428, 8.8639],
        'Bjerringbro' => [56.3756, 9.6550],
        'Vojens' => [55.2483, 9.3050],
        'Skagen' => [57.7222, 10.5878],
        'Ebeltoft' => [56.2000, 10.6800],
        'Bramming' => [55.4650, 8.7044],
        'Hammel' => [56.2500, 9.8667],
        'Slangerup' => [55.8467, 12.1761],
        'Ringe' => [55.2369, 10.4794],
        'Fåborg' => [55.0950, 10.2422],
        'Gilleleje' => [56.1225, 12.3081],
        'Hornslet' => [56.3150, 10.3192],
        'Aabybro' => [57.1625, 9.7306],
        'Børkop' => [55.6419, 9.6519],
        'Skælskør' => [55.2528, 11.2931],
        'Rødekro' => [55.0701, 9.3350],
        'Kerteminde' => [55.4490, 10.6590],
        'Assens' => [55.2664, 9.8968],
        'Bellinge' => [55.3350, 10.3133],
        'Maribo' => [54.7753, 11.5035],
        'Hellebæk' => [56.0671, 12.5593],
        'Nordborg' => [55.0577, 9.7476],
        'Nibe' => [56.9833, 9.6333],
        'Tune' => [55.5931, 12.1708],
        'Klarup' => [57.0125, 10.0597],
        'Munkebo' => [55.4570, 10.5530],
        'Hirtshals' => [57.5900, 9.9600],
        'Strib' => [55.5367, 9.7722],
        'Otterup' => [55.5167, 10.4000],
        'Kjellerup' => [56.2847, 9.4331],
        'Fensmark' => [55.2778, 11.8050],
        'Hornbæk' => [56.0875, 12.4587],
        'Mårslet' => [56.0683, 10.1617],
        'Viby' => [55.5475, 12.0250],
        'Strøby Egede' => [55.4131, 12.2456],
        'Hadsund' => [56.7185, 10.1155],
        'Borup' => [55.4983, 11.9781],
        'Nykøbing Sjælland' => [55.9225, 11.6686],
        'Vamdrup' => [55.4270, 9.2830],
        'Kirke Hvalsø' => [55.5958, 11.8616],
        'Solbjerg' => [56.0397, 10.0867],
        'Vodskov' => [57.1083, 10.0269],
        'Brørup' => [55.4825, 9.0158],
        'Havdrup' => [55.5433, 12.1181],
        'Sakskøbing' => [54.7975, 11.6364],
        'Hjallerup' => [57.1675, 10.1450],
        'Rudkøbing' => [54.9380, 10.7160],
        'Langeskov' => [55.3568, 10.5862],
        'Årslev' => [55.3029, 10.4642],
        'Høng' => [55.5044, 11.2919],
        'Videbæk' => [56.0931, 8.6325],
        'Jyderup' => [55.6575, 11.4014],
        'Gråsten' => [54.9211, 9.5944],
        'Svogerslev' => [55.6339, 12.0111],
        'Jægerspris' => [55.8500, 11.9910],
        'Lynge' => [55.8397, 12.2767],
        'Fakse' => [55.2544, 12.1181],
        'Vildbjerg' => [56.1972, 8.7667],
        'Tarm' => [55.9053, 8.5206],
        'Dianalund' => [55.5295, 11.4924],
        'Jelling' => [55.7558, 9.4194],
        'Bogense' => [55.5642, 10.0894],
        'Tølløse' => [55.6117, 11.7736],
        'Harlev' => [56.1447, 9.9969],
        'Juelsminde' => [55.7062, 10.0155],
        'Løgstør' => [56.9667, 9.2500],
        'Brædstrup' => [55.9717, 9.6114],
        'Assentoft' => [56.4403, 10.1467],
        'Præstø' => [55.1167, 12.0500],
        'Virklund' => [56.1297, 9.5600],
        'Stege' => [54.9861, 12.2856],
        'Gistrup' => [56.9964, 9.9906],
        'Græsted' => [56.0653, 12.2842],
        'Nexø' => [55.0625, 15.1319],
        'Store Heddinge' => [55.3086, 12.3869],
        'Hjortshøj' => [56.2492, 10.2656],
        'Taulov' => [55.5364, 9.6083],
        'Sabro' => [56.2133, 10.0344],
        'Storvorde' => [57.0028, 10.1008],
        'Trige' => [56.2533, 10.1475],
        'Auning' => [56.4306, 10.3767],
        'Rønde' => [56.3000, 10.4833],
        'Søndersø' => [55.4858, 10.2549],
        'Glamsbjerg' => [55.2778, 10.1056],
        'Vissenbjerg' => [55.3862, 10.1317],
        'Årup' => [55.3798, 10.0482],
        'Bjæverskov' => [55.4575, 12.0319],
        'Thurø By' => [55.0456, 10.6672],
        'Broager' => [54.8890, 9.6696],
        'Vallensbæk Strand' => [55.6353, 12.3648],
        'Frederiksberg' => [55.6785, 12.5221],
        'Herlev' => [55.7235, 12.4404],
        'Kongens Lyngby' => [55.7718, 12.5060],
        'Søborg' => [55.7302, 12.5098],
        'Hvidovre' => [55.6503, 12.4758],
        'Rødovre' => [55.6827, 12.4644],
        'Charlottenlund' => [55.7537, 12.5918],
        'Ballerup' => [55.7198, 12.3520],
        'Glostrup' => [55.6666, 12.4038],
        'Brøndby' => [55.6541, 12.4215],
        'Albertslund' => [55.6623, 12.3351],
        'Ishøj' => [55.6184, 12.3281],
        'Allerød' => [55.8703, 12.3574],
        'Holte' => [55.8167, 12.4667],
        'Greve' => [55.5966, 12.2492],
        'Kokkedal' => [55.9098, 12.5152],
        'Kastrup' => [55.6352, 12.6489],
        'Stenløse' => [55.7677, 12.1960],
        'Ærøskøbing' => [54.8912, 10.4083],
        'Virum' => [55.7948, 12.4510],
    ];

    /** Alternative spellings that map to an entry in CITIES (lowercase keys). */
    private const ALIASES = [
        'københavn' => 'Copenhagen',
        'århus' => 'Aarhus',
    ];

    /** Lowercase name/alias => canonical CITIES key. */
    private static ?array $names = null;

    /** Raw city string => resolved coordinates (or null), memoised per process/request. */
    private static array $resolved = [];

    /**
     * @return array{0: float, 1: float}|null [latitude, longitude]
     */
    public static function coordinates(?string $city): ?array
    {
        if ($city === null || trim($city) === '') {
            return null;
        }

        if (array_key_exists($city, self::$resolved)) {
            return self::$resolved[$city];
        }

        return self::$resolved[$city] = self::lookup($city);
    }

    private static function lookup(string $city): ?array
    {
        $city = mb_strtolower(trim($city));

        // Strip a leading postal code, e.g. "2100 København Ø", "1500-1799 København V" or "DK-2100 København".
        $city = (string) preg_replace('/^(?:[a-z]{2}-)?\d{4}(-\d{4})?[\/\s]+/u', '', $city);

        $names = self::names();

        // Exact match
        if (isset($names[$city])) {
            return self::CITIES[$names[$city]];
        }

        // Otherwise find the longest city name contained in the string on word
        // boundaries, so "København Ø" -> "København" while "Helsingør" does
        // not match "Helsinge" (the character after a match must not be a letter).
        $best = null;
        $cityLength = mb_strlen($city);

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

            if ($end < $cityLength && preg_match('/\pL/u', mb_substr($city, $end, 1))) {
                continue;
            }

            $best = $name;
        }

        return $best !== null ? self::CITIES[$names[$best]] : null;
    }

    /**
     * @return array<string, string>
     */
    private static function names(): array
    {
        if (self::$names !== null) {
            return self::$names;
        }

        $names = [];

        foreach (self::CITIES as $name => $coords) {
            $names[mb_strtolower($name)] = $name;
        }

        foreach (self::ALIASES as $alias => $canonical) {
            $names[mb_strtolower($alias)] = $canonical;
        }

        return self::$names = $names;
    }
}
