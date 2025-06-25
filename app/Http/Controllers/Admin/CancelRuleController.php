<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancellationRuleRequest;
use App\Models\CancellationRule;
use App\Models\Rate;
use Illuminate\Http\Request;

class CancelRuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-rule|edit-rule|delete-rule', ['only' => ['index','show']]);
        $this->middleware('permission:create-rule', ['only' => ['create','store']]);
        $this->middleware('permission:edit-rule', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-rule', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $hotel_id = $request->session()->get('hotel_id');
        $rules = CancellationRule::where('hotel_id', $hotel_id)->paginate(20);
        return view('auth.cancellations.index', compact('rules'));
    }

    public function create(Request $request)
    {
        $hotel_id = $request->session()->get('hotel_id');
        $rates = Rate::where('hotel_id', $hotel_id)->get();
        return view('auth.cancellations.form', compact('hotel_id', 'rates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CancellationRuleRequest $request)
    {
        $params = $request->all();
        CancellationRule::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));

        session()->flash('success', 'CancellationRule ' . $request->title . ' created');
        return redirect()->route('cancellations.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(CancellationRule $cancellation, Request $request)
    {
        $hotel_id = $request->session()->get('hotel_id');
        $rates = Rate::where('hotel_id', $hotel_id)->get();
        return view('auth.cancellations.form', compact('cancellation', 'hotel_id', 'rates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CancellationRuleRequest $request, CancellationRule $cancellation)
    {
        $params = $request->all();
        $cancellation->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'CancellationRule ' . $request->title . ' updated');
        return redirect()->route('cancellations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CancellationRule $cancellation)
    {

        $cancellation->delete();
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'CancellationRule ' . $cancellation->title . ' deleted');
        return redirect()->route('cancellations.index');
    }
}
