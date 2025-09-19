<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Models\Image;

class FunctionsService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.tm_base_url');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getUtcOffsetByCountryCode($CountryCode){
        // ISO 3166-1 alpha-2
        $tzList = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $CountryCode);

        $timezone = new DateTimeZone($tzList[0]);
        $now = new DateTime('now', $timezone);
        $offsetInSeconds = $timezone->getOffset($now);
        $offsetFormatted = sprintf('%s%02d:%02d',
            $offsetInSeconds < 0 ? '-' : '+',
            abs($offsetInSeconds) / 3600,
            (abs($offsetInSeconds) % 3600) / 60
        );

        return $offsetFormatted;
    }

    public function getCountryCodes()
    {
        return [
            "AF", "AL", "DZ", "AD", "AO", "AG", "AR", "AM", "AU", "AT", "AZ",
            "BS", "BH", "BD", "BB", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA",
            "BW", "BR", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF",
            "TD", "CL", "CN", "CO", "KM", "CG", "CD", "CR", "CI", "HR", "CU",
            "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "GQ", "ER",
            "EE", "SZ", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH",
            "GR", "GD", "GT", "GN", "GW", "GY", "HT", "HN", "HU", "IS", "IN",
            "ID", "IR", "IQ", "IE", "IL", "IT", "JM", "JP", "JO", "KZ", "KE",
            "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LR", "LY", "LI", "LT",
            "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MH", "MR", "MU", "MX",
            "FM", "MD", "MC", "MN", "ME", "MA", "MZ", "MM", "NA", "NR", "NP",
            "NL", "NZ", "NI", "NE", "NG", "KP", "NO", "OM", "PK", "PW", "PA",
            "PG", "PY", "PE", "PH", "PL", "PT", "QA", "RO", "RU", "RW", "KN",
            "LC", "VC", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG",
            "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SD", "SR", "SE",
            "CH", "SY", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR",
            "TM", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VA", "VE",
            "VN", "YE", "ZM", "ZW"
        ];
    }
}