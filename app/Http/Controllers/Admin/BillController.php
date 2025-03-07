<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-bill|edit-bill|delete-bill', ['only' => ['index','show']]);
        $this->middleware('permission:create-bill', ['only' => ['create','store']]);
        $this->middleware('permission:edit-bill', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-bill', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $bills = Bill::where('hotel_id', $hotel)->get();
        return view('auth.bills.index', compact('bills', 'hotel'));
    }

    public function booking(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $bookings = Book::where('hotel_id', $hotel)->get();
        return view('auth.bills.booking', compact('bookings', 'hotel'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.bills.form', compact('hotel'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $params = $request->all();
        unset($params['file1']);
        if ($request->has('file1')) {
            $params['file1'] = $request->file('file1')->store('bills');
        }
        unset($params['file2']);
        if ($request->has('file2')) {
            $params['file2'] = $request->file('file2')->store('bills');
        }

        Bill::create($params);
        session()->flash('success', 'Соглашение ' . $request->title . ' добавлено');
        return redirect()->route('bills.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Bill $bill)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.bills.form', compact('bill', 'hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        $params = $request->all();

        unset($params['file1']);
        if ($request->has('file1')) {
            Storage::delete($bill->file1);
            $params['file1'] = $request->file('file1')->store('bills');
        }
        unset($params['file2']);
        if ($request->has('file2')) {
            Storage::delete($bill->file2);
            $params['file2'] = $request->file('file2')->store('bills');
        }


        $bill->update($params);
        session()->flash('success', 'Соглашение ' . $request->title . ' обновлено');
        return redirect()->route('bills.index');
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();
        session()->flash('success', 'Счет ' . $bill->title . ' удален');
        return redirect()->route('bills.index');
    }
}
