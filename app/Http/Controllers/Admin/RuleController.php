<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RuleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-rule|edit-rule|delete-rule', ['only' => ['index','show']]);
        $this->middleware('permission:create-rule', ['only' => ['create','store']]);
        $this->middleware('permission:edit-rule', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-rule', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $rules = Rule::where('hotel_id', $hotel)->paginate(10);
        return view('auth.rules.index', compact('hotel', 'rules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $rooms = Room::where('hotel_id', $hotel)->get();
        return view('auth.rules.form', compact('rooms', 'hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RuleRequest $request)
    {
        $params = $request->all();
        Rule::create($params);
        //Mail::to('info@timmedia.store')->send(new RoomCreateMail($request));

        session()->flash('success', 'Rule ' . $request->title . ' created');
        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, Rule $rule)
    {
        $hotel = $request->session()->get('hotel_id');
        $rooms = Room::where('hotel_id', $hotel)->get();
        return view('auth.rules.form', compact('rule', 'hotel', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RuleRequest $request, Rule $rule)
    {
        $params = $request->all();
        $rule->update($params);
        //Mail::to('info@timmedia.store')->send(new RoomUpdateMail($request));
        session()->flash('success', 'Rule ' . $request->title . ' updated');
        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rule $rule)
    {

        $rule->delete();
        Category::where('rule_id', $rule->id)->update(['rule_id' => null]);
        //Mail::to('info@timmedia.store')->send(new RoomDeleteMail($room));
        session()->flash('success', 'Rule ' . $rule->title . ' deleted');
        return redirect()->route('categories.index');
    }
}
