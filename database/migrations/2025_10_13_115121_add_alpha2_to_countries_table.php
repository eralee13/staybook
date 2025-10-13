<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->char('alpha2', 2)
                ->nullable()
                ->unique()
                ->after('code');
        });

        // --- Заполним ISO alpha2-коды ---
        $map = [
            'RUS' => 'RU', 'KGZ' => 'KG', 'KAZ' => 'KZ', 'UZB' => 'UZ', 'TJK' => 'TJ', 'TKM' => 'TM',
            'UKR' => 'UA', 'BLR' => 'BY', 'ARM' => 'AM', 'AZE' => 'AZ', 'GEO' => 'GE', 'MDA' => 'MD',
            'USA' => 'US', 'GBR' => 'GB', 'DEU' => 'DE', 'FRA' => 'FR', 'ESP' => 'ES', 'ITA' => 'IT',
            'CHN' => 'CN', 'IND' => 'IN', 'JPN' => 'JP', 'KOR' => 'KR', 'VNM' => 'VN',
            'ARE' => 'AE', 'SAU' => 'SA', 'TUR' => 'TR', 'EGY' => 'EG', 'ISR' => 'IL',
            'CAN' => 'CA', 'AUS' => 'AU', 'BRA' => 'BR', 'ARG' => 'AR', 'MEX' => 'MX',
        ];

        foreach ($map as $iso3 => $alpha2) {
            DB::table('countries')->where('code', $iso3)->update(['alpha2' => $alpha2]);
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropUnique(['alpha2']);
            $table->dropColumn('alpha2');
        });
    }
};