<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelRequest;
use App\Mail\HotelDeleteMail;
use App\Mail\HotelMail;
use App\Mail\HotelUpdateMail;
use App\Models\Amenity;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Image;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-hotel|edit-hotel|delete-hotel', ['only' => ['index','show']]);
        $this->middleware('permission:create-hotel', ['only' => ['create','store']]);
        $this->middleware('permission:edit-hotel', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-hotel', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user()->id;
        $chotel = Hotel::all();
        if($user != 1 && $user != 3){
            $hotels = Hotel::where('user_id', $user)->paginate(20);
        } else{
            $hotels = Hotel::paginate(20);
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
        return view('auth.hotels.form', compact('cities', 'timezones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HotelRequest $request)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        unset($params['image']);
        if ($request->has('image')) {
            $path = $request->file('image')->store('hotels');
            $params['image'] = $path;
        }
        $hotel = Hotel::create($params);

        $images = $request->file('images');
        if ($request->hasFile('images')) :
            foreach ($images as $image):
                $image = $image->store('hotels');
                DB::table('images')->insert(
                    array(
                        'image' => $image,
                        'hotel_id' => $hotel->id,
                    )
                );
            endforeach;
        endif;

        DB::table('amenities')->insert(
            array(
                'hotel_id' => $hotel->id,
                'services' => '1',
            )
        );
        DB::table('payments')->insert(
            array(
                'hotel_id' => $hotel->id,
                'payments' => '1',
            )
        );

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

        DB::table('bills')->insert(
            array(
                'title' => $hotel->title,
                'agreement' => $pathname,
                'rules' => $pathname2,
                'hotel_id' => $hotel->id,
                'status' => 1,
                'created_at' => date('Y-m-d H:s:i'),
                'updated_at' => date('Y-m-d H:s:i')
            )
        );

        //Mail::to('info@timmedia.store')->send(new HotelMail($request));

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

        return view('auth.hotels.form', compact('hotel', 'images', 'cities', 'timezones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HotelRequest $request, Hotel $hotel)
    {
        $request['code'] = Str::slug($request->title);
        $params = $request->all();
        unset($params['image']);
        if ($request->has('image')) {
            Storage::delete($hotel->image);
            $params['image'] = $request->file('image')->store('hotels');
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

        //Mail::to('info@timmedia.store')->send(new HotelUpdateMail($request));

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
        if($hotel->image){
            Storage::delete($hotel->image);
        }
        $images = Image::where('hotel_id', $hotel->id)->get();
        if($images->isNotEmpty()){
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
        //Mail::to('info@timmedia.store')->send(new HotelDeleteMail($hotel));
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
                                    <a href="<?php echo route('hotels.show', $row->id) ?>" class="more"><i
                                            class="fa-regular
                                fa-pen-to-square"></i> Choose</a>
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
