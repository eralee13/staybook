<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelRequest;
use App\Mail\HotelActivatedMail;
use App\Mail\HotelDeleteMail;
use App\Mail\HotelNotificationMail;
use App\Mail\HotelUpdateMail;
use App\Mail\HotelUserMail;
use App\Models\Amenity;
use App\Models\City;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Image;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-hotel|edit-hotel|delete-hotel', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-hotel', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-hotel', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-hotel', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user()->id;
        $chotel = Hotel::all();
        if ($user != 1 && $user != 3) {
            $hotels = Hotel::where('user_id', $user)->latest()->paginate(20);
        } else {
            $hotels = Hotel::latest()->paginate(20);
        }

        return view('auth.hotels.index', compact('hotels', 'chotel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = City::where('country_id', null)->get();
        $timezones = DateTimeZone::listIdentifiers();
        $serviceCategories = [
            'Services' => [
                'Car rental',
                'Concierge service',
                'Currency exchange',
                'Dry сleaning',
                'Ironing service',
                'Laundry',
                'ATM',
                'Shoe shine',
                'Bicycle rental',
                'Free bicycle rental',
                'Private check-in/check-out',
                'Ticket service',
                'Tour desk',
                'Trouser press',
                'Late check-out available',
                'Early check-in',
                'Express check-out',
                'Express check-in',
                'Tailor shop',
                'Doorman',
                'Security guard',
                'Express check-in/check-out',
                'Coffee/tea for guests',
                'Iron',
                'Bathrobe (on request)',
                'Luggage storage',
            ],
            'Sports and Leisure' => [
                'Fitness centre',
                'Boating/Canoeing',
                'Casino',
                'Cycling',
                'Darts',
                'Diving',
                'Fishing',
                'Golf course (within 3 km)',
                'Horse riding',
                'Karaoke',
                'Mini golf',
                'Nightclub/DJ',
                'BBQ facilities',
                'Billiards',
                'Squash',
                'Table tennis',
                'Water sports facilities',
                'Windsurfing',
                'Entertainment',
                'Hiking',
                'Snorkelling',
                'Tennis court',
                'Library',
                'Hunt',
                'Rock Climbing',
                'Museum',
                'Barbeque',
                'Picnic area',
                'Barbecue grill(s)',
                'Badminton',
                '24 - hour gym',
                'Sailing',
                'Boating',
                'Golf course',
                'Gym',
                'Yachting',
            ],
            'General' => [
                'Newspapers',
                'Designated smoking areas',
                'Bridal suite',
                'Chapel/shrine',
                'Garden',
                'Baggage storage',
                'Non-smoking rooms',
                'Safe',
                'Shops on site',
                'Soundproof rooms',
                'Allergy free rooms',
                'Souvenir shop',
                'Non-smoking hotel',
                'Heating',
                'Sunbathing terrace',
                'Air conditioner',
                'Design hotel',
                'Terrace',
                'Shared kitchen',
                'Refrigerator',
                'Washing machine',
                'Ironing facilities',
                'Shared fridge',
                'Hairdryer (upon request)',
                'Bank',
                'Lockers',
                'Shared living room',
                'Telephone',
                'Microwave oven',
                'Dishwasher',
                'Conference Hall',
            ],
        ];
        return view('auth.hotels.form', compact('cities', 'timezones', 'serviceCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HotelRequest $request)
    {
        $params = $request->except(['image', 'images']); // исключаем поля без колонок
        $params['code'] = Str::slug($request->title);
        unset($params['services']);

        // загрузка главного изображения
        if ($request->hasFile('image')) {
            $params['image'] = $request->file('image')->store('hotels', 'public');
        }

        // создаём отель
        $hotel = Hotel::create($params);

        // загрузка дополнительных изображений
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('hotels', 'public');

                DB::table('images')->insert([
                    'hotel_id' => $hotel->id,
                    'image' => $path,
                ]);
            }
        }

        if ($request->has('services')) {
            $params = [
                'title' => $request->title,
                'hotel_id' => $hotel->id,
                'services' => implode(', ', $request->input('services', [])),
            ];
            Amenity::create($params);
        }


        // pdf-файлы
        $data = [
            'date' => date('d.m.Y'),
            'user' => Auth::user()
        ];
        $agreementPath = 'pdf/agreement_' . $hotel->id . '.pdf';
        $rulesPath = 'pdf/rules_' . $hotel->id . '.pdf';

        $agreementPdf = PDF::loadView('pdf.agreement', $data);
        $rulesPdf = PDF::loadView('pdf.rules', $data);

        Storage::put($agreementPath, $agreementPdf->output());
        Storage::put($rulesPath, $rulesPdf->output());

        // запись в bills
        DB::table('bills')->insert([
            'title' => $hotel->title,
            'agreement' => $agreementPath,
            'rules' => $rulesPath,
            'hotel_id' => $hotel->id,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // отправка email
        try {
            $admin_email = Contact::first()->email;
            App::getLocale();

            $adminEmails = [$admin_email, '02_07_92@mail.ru'];
            foreach ($adminEmails as $email) {
                Mail::to($email)->send(new HotelNotificationMail($hotel));
            }

            Mail::to($hotel->user->email)->send(new HotelUserMail($hotel));

        } catch (\Exception $e) {
            Log::error('Ошибка при отправке email: ' . $e->getMessage());
        }

        session()->flash('success', $request->title . ' added');
        return redirect()->route('hotels.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Hotel $hotel)
    {
        $users = Auth::user();
        $images = Image::where('hotel_id', $hotel->id)->get();
        $request->session()->put('hotel_id', $hotel->id);
        $amenity = Amenity::firstOrFail();
        //dd($request->session()->get('hotel_id'));
        return view('auth.hotels.show', compact('hotel', 'users', 'images', 'amenity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            return redirect()->route('hotels.index')->with('error', 'Сначала выберите отель');
        }
        $cities = City::orderBy('title', 'ASC')->get();
        $images = Image::where('hotel_id', $hotel->id)->get();
        $timezones = \DateTimeZone::listIdentifiers();

        $serviceCategories = [
            'Services' => [
                'Car rental',
                'Concierge service',
                'Currency exchange',
                'Dry сleaning',
                'Ironing service',
                'Laundry',
                'ATM',
                'Shoe shine',
                'Bicycle rental',
                'Free bicycle rental',
                'Private check-in/check-out',
                'Ticket service',
                'Tour desk',
                'Trouser press',
                'Late check-out available',
                'Early check-in',
                'Express check-out',
                'Express check-in',
                'Tailor shop',
                'Doorman',
                'Security guard',
                'Express check-in/check-out',
                'Coffee/tea for guests',
                'Iron',
                'Bathrobe (on request)',
                'Luggage storage',
            ],
            'Sports and Leisure' => [
                'Fitness centre',
                'Boating/Canoeing',
                'Casino',
                'Cycling',
                'Darts',
                'Diving',
                'Fishing',
                'Golf course (within 3 km)',
                'Horse riding',
                'Karaoke',
                'Mini golf',
                'Nightclub/DJ',
                'BBQ facilities',
                'Billiards',
                'Squash',
                'Table tennis',
                'Water sports facilities',
                'Windsurfing',
                'Entertainment',
                'Hiking',
                'Snorkelling',
                'Tennis court',
                'Library',
                'Hunt',
                'Rock Climbing',
                'Museum',
                'Barbeque',
                'Picnic area',
                'Barbecue grill(s)',
                'Badminton',
                '24 - hour gym',
                'Sailing',
                'Boating',
                'Golf course',
                'Gym',
                'Yachting',
            ],
            'General' => [
                'Newspapers',
                'Designated smoking areas',
                'Bridal suite',
                'Chapel/shrine',
                'Garden',
                'Baggage storage',
                'Non-smoking rooms',
                'Safe',
                'Shops on site',
                'Soundproof rooms',
                'Allergy free rooms',
                'Souvenir shop',
                'Non-smoking hotel',
                'Heating',
                'Sunbathing terrace',
                'Air conditioner',
                'Design hotel',
                'Terrace',
                'Shared kitchen',
                'Refrigerator',
                'Washing machine',
                'Ironing facilities',
                'Shared fridge',
                'Hairdryer (upon request)',
                'Bank',
                'Lockers',
                'Shared living room',
                'Telephone',
                'Microwave oven',
                'Dishwasher',
                'Conference Hall',
            ],
        ];

        $amenity = Amenity::where('hotel_id', $hotel->id)->first();

        $amenities = [];

        if ($amenity && $amenity->services) {
            $amenities = array_map('trim', explode(',', $amenity->services));
        }

        return view('auth.hotels.form', compact('hotel', 'images', 'cities', 'timezones', 'amenity', 'amenities', 'serviceCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HotelRequest $request, Hotel $hotel)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();

        unset($params['services']);
        // Обновляем или создаём Amenity
        if ($request->has('services')) {
            $par = [
                'title' => $request->title,
                'hotel_id' => $hotel->id,
                'services' => implode(', ', $request->services),
            ];

            Amenity::updateOrCreate(
                ['hotel_id' => $hotel->id],
                $par
            );
        }

        unset($params['image']);
        if ($request->has('image')) {
            Storage::delete($hotel->image);
            $params['image'] = $request->file('image')->store('hotels');
        }

        unset($params['delete_images']);
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $imageId) {
                $image = \App\Models\Image::find($imageId);
                if ($image) {
                    Storage::delete($image->image); // удаление из хранилища
                    $image->delete();               // удаление из БД
                }
            }
        }


        //images
        unset($params['images']);
        $images = $request->file('images');
        if ($request->hasFile('images')) {
//            $dimages = Image::where('hotel_id', $hotel->id)->get();
//            if ($dimages != null) {
//                foreach ($dimages as $image) {
//                    Storage::delete($image->image);
//                }
//                DB::table('images')->where('hotel_id', $hotel->id)->delete();
//            }
            foreach ($images as $image):
                $image = $image->store('hotels');
                DB::table('images')
                    ->where('hotel_id', $hotel->id)
                    ->updateOrInsert(['hotel_id' => $hotel->id, 'image' => $image]);
            endforeach;
        }

        if ($hotel->status == 0 && $request->input('status') == 1) {
            Mail::to($hotel->user->email)->send(new HotelActivatedMail($hotel));
        }

        $hotel->update($params);

        //pdf
        $data = [
            'date' => date('d.m.Y'),
            'user' => Auth::user()
        ];
        $pdf = PDF::loadView('pdf.agreement', $data);
        $pathname = 'pdf/agreement_' . $hotel->id . '.pdf';
        Storage::put($pathname, $pdf->output());

        // cancellations
        $pdf2 = PDF::loadView('pdf.rules', $data);
        $pathname2 = 'pdf/rules_' . $hotel->id . '.pdf';
        Storage::put($pathname2, $pdf2->output());

        DB::table('bills')
            ->where('hotel_id', $hotel->id)
            ->update([
                'agreement' => $pathname,
                'rules' => $pathname2,
            ]);

        $email = Contact::first()->email;
        Mail::to($email)->send(new HotelUpdateMail($request));

        session(['hotel_id' => $request->hotel_id]);

        session()->flash('success', $request->title . ' updated');
        return redirect()->route('hotels.show', $hotel);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        if ($hotel->image) {
            Storage::delete($hotel->image);
        }
        $images = Image::where('hotel_id', $hotel->id)->get();
        if ($images->isNotEmpty()) {
            foreach ($images as $image) {
                Storage::delete($image->image);
            }
            DB::table('images')->where('hotel_id', $hotel->id)->delete();
        }
        //$bill = Bill::where('hotel_id', $hotel->id)->firstOrFail();
        //Storage::delete($bill->agreement);
        //Storage::delete($bill->cancellations);

        DB::table('bills')->where('hotel_id', $hotel->id)->delete();
        DB::table('rooms')->where('hotel_id', $hotel->id)->delete();
        DB::table('rates')->where('hotel_id', $hotel->id)->delete();
        DB::table('amenities')->where('hotel_id', $hotel->id)->delete();
        DB::table('payments')->where('hotel_id', $hotel->id)->delete();
        $email = Contact::first()->email;
        Mail::to($email)->send(new HotelDeleteMail($hotel));
        session()->flash('success', 'Property ' . $hotel->title . ' deleted');
        return redirect()->route('hotels.index');
    }

    public function search(Request $request)
    {
        if ($request->ajax()) {
            $data = Hotel::where('id', 'like', '%' . $request->search . '%')
                ->orwhere('title', 'like', '%' . $request->search . '%')
                ->orwhere('address', 'like', '%' . $request->search . '%')
                ->orwhere('title_en', 'like', '%' . $request->search . '%')->get();
            if (count($data) > 0) { ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo $row->id ?></td>
                            <td><?php echo $row->title ?></td>
                            <td><?php echo $row->address ?></td>
                            <td>
                                <ul>
                                    <a href="<?php echo route('hotels.show', $row->id) ?>"><img
                                                src="<?php echo route('index') ?>/img/icons/eye.svg" alt=""></a>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <h2>No results</h2>

                <?php
            }
        }
    }
}
