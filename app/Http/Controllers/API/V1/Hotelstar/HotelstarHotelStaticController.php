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
use App\Models\Amenity;
use App\Services\FunctionsService;



class HotelstarHotelStaticController extends Controller
{
    protected $functionsService;
    public $apiKey, $url, $coef;

    public function __construct(FunctionsService $functionsService)
    {
        $this->apiKey = config('app.hotelstar_api_key');
        $this->url = config('app.hotelstar_api_url');
        $this->coef = config('app.main_coef');
        $this->functionsService = $functionsService;
    }

    public function HSHotelStatic()
    {
        $this->extractJson();
    }

   public function extractJson()
    {
        // set_time_limit(1800);

        $url = 'https://dev.hotelstar.ru/dump/raw/hotel.tar.gz';
        // $url = 'https://hotelstar.ru/dump/raw/hotel.tar.gz';

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $gzPath  = $tmpDir.'/hotel.tar.gz';
        $tarPath = $tmpDir.'/hotel.tar';
        $outDir  = $tmpDir.'/hotel';

        // 1. Скачиваем архив в файл
        // Http::withOptions([
        //     'sink' => $gzPath,
        //     'timeout' => 600,          // максимальное время выполнения запроса (секунды)
        //     'connect_timeout' => 60,   // время ожидания соединения
        // ])->get($url);

        // 2. Снимаем gzip → получаем .tar
        // $this->gunzip($gzPath, $tarPath);

        // 3. Распаковываем tar в папку
        try {
            // if (!is_dir($outDir)) mkdir($outDir, 0775, true);

            // // Распаковать через shell
            // exec("tar -xzf " . escapeshellarg($gzPath) . " -C " . escapeshellarg($outDir));

            // 4. Читаем hotel.json
            $jsonFile = $outDir.'/hotel.json';
            if (!file_exists($jsonFile)) {
                log::channel('hotelstar')->error('Hotel Static Файл hotel.json не найден');
                return response()->json(['error' => 'Файл hotel.json не найден'], 404);
            }

            $cityController = new HotelstarCityStaticController();
            $cityFile = $cityController->extractJson();
            $cityData;

            if ( $cityFile ){
                $path = storage_path('app/tmp/city/city.json');

                // читаем файл как строки
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // объединяем строки через запятую
                $jsonString = "[" . implode(",", $lines) . "]";
                
                // пробуем декодировать
                $cityData = collect(json_decode($jsonString, true))
                ->keyBy('id')   // делаем ключами id
                ->toArray();

                if (json_last_error() !== JSON_ERROR_NONE) {
                    die("Ошибка JSON: " . json_last_error_msg());
                }
                
            }

            $countryController = new HotelstarCountryStaticController();
            $countryFile = $countryController->extractJson();
            $countryData;

            if ( $countryFile ){
                $path = storage_path('app/tmp/country/country.json');

                // читаем файл как строки
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // объединяем строки через запятую
                $jsonString = "[" . implode(",", $lines) . "]";
                
                // пробуем декодировать
                $countryData = collect(json_decode($jsonString, true))
                ->keyBy('id')   // делаем ключами id
                ->toArray();

                if (json_last_error() !== JSON_ERROR_NONE) {
                    die("Ошибка JSON: " . json_last_error_msg());
                }
                
            }

            // dd($cityData[155981]);

            $controller = new HotelstarCategoryStaticController();
            $category = $controller->extractJson();
            $withKeys = collect($category->getData(true))->keyBy('id')->toArray();
            
            $count = 0;
            $data = [];
            $handle = fopen($jsonFile, 'r');

            if ($handle) {
                while (($line = fgets($handle)) !== false ){ //&& $count < 5
                    $line = trim($line);
                    if ($line === '') continue;

                    $item = json_decode($line, true);
                    if ($item !== null && $item['cityId'] == 67005 && $count < 5) {
                        $data[] = $item;
                        
                        $kind; $rating = 0;
                        if($item['categoryId'] > 5){
                            $kind = $withKeys[$item['categoryId']]['nameEn'] ?? '';
                        }else{
                            $rating = $item['categoryId'];
                        }

                        if( $cityData && $countryData){
                            $alfa2 = $countryData[$cityData[$item['cityId']]['countryId']]['alfa2'] ?? '';
                            $utc = $this->functionsService->getUtcOffsetByCountryCode($alfa2);
                        }
                        
                        $code  = str_replace([' ', '/'], '_', strtolower($item['nameEn']) ?? '');
                        $amenitiesRoom = ''; $descriptionRoom = ''; $amenitiesHotel = '';

                            $hotel = Hotel::updateOrCreate(
                                ['hotelstar_id' => $item['id']],
                                [
                                    'code' => $code ?? '',
                                    'title' => $item['nameRu'] ?? '',
                                    'title_en' => $item['nameEn'] ?? '',
                                    'type' => $kind ?? '',
                                    'rating' => (int) ($rating ?? 0),
                                    'address_en' => $item['addressEn'] ?? '',
                                    // 'country_code' => $data['region']['country_code'] ?? '',
                                    'city' => $cityData[$item['cityId']]['nameEn'] ?? '',
                                    'utc' => $utc ?? '',
                                    'lat' => $item['latitude'] ?? '',
                                    'lng' => $item['longitude'] ?? '',
                                    'checkin' => $item['check_in_time'] ?? '',
                                    'checkout' => $item['check_out_time'] ?? '',
                                    'phone' => $item['phone'] ?? '',
                                    'email' => $item['email'] ?? '',
                                    'description' => $item['description']['descriptionRu'] ?? '',
                                    'description_en' => $item['description']['descriptionEn'] ?? '',
                                    'image' => '',
                                    'hotelstar_id' => $item['id'],
                                    'status' => 1,
                                    'user_id' => 1,
                                ]
                            );
                            
                            // // Обновляем удобства в таблице amenities
                            Amenity::updateOrCreate(
                                ['hotel_id' => $hotel->id],
                                ['title' => 'Services', 'services' => $amenitiesHotel ?? '']
                            );
                
                            $room = Room::updateOrCreate(
                                ['hotel_id' => $hotel->id],
                                [
                                    'title' => '',
                                    'title_en' => '',
                                    'services' => $amenitiesRoom,
                                    // 'image' => $localImagePath,
                                    'description_en' => $descriptionRoom ?? ''
                                ]
                            );

                        $count++;
                        echo "Processed hotel ID: {$item['id']} - {$item['nameRu']} \n";
                    }
                }
                fclose($handle);
            }

            // dd($data);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log::channel('hotelstar')->error('Hotel Static Ошибка JSON: '.json_last_error_msg());
                return response()->json(['error' => 'Ошибка JSON: '.json_last_error_msg()], 422);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            log::channel('hotelstar')->error('Hotel Static Ошибка распаковки: '.$e->getMessage());
            return response()->json(['error' => 'Ошибка распаковки: '.$e->getMessage()], 500);
        }

        
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