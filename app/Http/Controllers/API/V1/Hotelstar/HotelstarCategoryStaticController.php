<?php 

namespace App\Http\Controllers\API\V1\Hotelstar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PharData;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Models\Hotel;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;
use App\Models\CancellationRule;



class HotelstarCategoryStaticController extends Controller
{
    public $apiKey, $url;

    public function __construct()
    {
        $this->apiKey = config('app.hotelstar_api_key');
        $this->url = config('app.hotelstar_api_url');
        $this->coef = config('app.main_coef');
    }

    public function HSCategoryStatic()
    {
       return $this->extractJson();
    }

   public function extractJson()
    {
        // set_time_limit(600);
        
        $url = 'https://dev.hotelstar.ru/dump/raw/category.tar.gz';

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $gzPath  = $tmpDir.'/category.tar.gz';
        $tarPath = $tmpDir.'/category.tar';
        $outDir  = $tmpDir.'/category';

        // 1. Скачиваем архив в файл
        Http::withOptions([
            'sink' => $gzPath,
            'timeout' => 600,          // максимальное время выполнения запроса (секунды)
            'connect_timeout' => 60,   // время ожидания соединения
        ])->get($url);

        // 2. Снимаем gzip → получаем .tar
        $this->gunzip($gzPath, $tarPath);

        // 3. Распаковываем tar в папку
        try {
            $phar = new \PharData($tarPath);
            $phar->extractTo($outDir, null, true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка распаковки: '.$e->getMessage()], 500);
        }

        // 4. Читаем room.json
        $jsonFile = $outDir.'/category.json';
        if (!file_exists($jsonFile)) {
            return response()->json(['error' => 'Файл room.json не найден'], 404);
        }

        $count = 0;
        $data = [];
        $handle = fopen($jsonFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false && $count < 10 ){
                $line = trim($line);
                if ($line === '') continue;

                $item = json_decode($line, true);
                if ($item !== null) {
                    $data[] = $item;

                    $count++;
                }
            }
            fclose($handle);
        }
        // dd($data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Ошибка JSON: '.json_last_error_msg()], 422);
        }

        return response()->json($data);
    }




    /**
     * Снимает gzip слой
     */
    private function gunzip(string $src, string $dest): bool
    {
        $gz = gzopen($src, 'rb');
        if (!$gz) return false;

        $out = fopen($dest, 'wb');
        if (!$out) {
            gzclose($gz);
            return false;
        }

        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 8192));
        }

        fclose($out);
        gzclose($gz);

        return true;
    }

}