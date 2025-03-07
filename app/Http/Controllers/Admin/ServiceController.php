<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;


class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-service|edit-service|delete-service', ['only' => ['index','show']]);
        $this->middleware('permission:create-service', ['only' => ['create','store']]);
        $this->middleware('permission:edit-service', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-service', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $services = Service::where('hotel_id', $hotel)->get();

        return view('auth.services.index', compact('services', 'hotel'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.services.form', compact('hotel'));
    }
    public function store(Request $request)
    {
        $params = [
            'title' => $request->title,
            'hotel_id' => $request->hotel_id,
            'services' => implode(', ', $request->services),
        ];
        Service::create($params);

        session()->flash('success', 'Услуги ' . $request->title . ' добавлены');
        return redirect()->route('services.index');
    }
    public function edit(Request $request, Service $service)
    {
        $hotel = $request->session()->get('hotel_id');
        $services = explode(', ', $service->services);
        return view('auth.services.form', compact('service', 'hotel', 'services'));
    }

    public function update(Request $request, Service $service)
    {
        $params = [
            'title' => $request->title,
            'hotel_id' => $request->hotel_id,
            'services' => implode(', ', $request->services),
        ];
        //$params = $request->all();
        $service->update($params);
        session()->flash('success', 'Услуги обновлены');

        return redirect()->route('services.index');
    }

}
