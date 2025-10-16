<?php

namespace App\Http\Controllers\API\V1\Emerging;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;

class EmergingTools
{
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
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
}
