<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-payment|edit-payment|delete-payment', ['only' => ['index','show']]);
        $this->middleware('permission:create-payment', ['only' => ['create','store']]);
        $this->middleware('permission:edit-payment', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-payment', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $payments = Payment::where('hotel_id', $hotel)->get();
        return view('auth.payments.index', compact('payments', 'hotel'));
    }

    public function create(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        return view('auth.payments.form', compact('hotel'));
    }
    public function store(Request $request)
    {
        $params = [
            'hotel_id' => $request->hotel_id,
            'payments' => implode(', ', $request->payments),
        ];
        Payment::create($params);

        session()->flash('success', 'Способы оплаты добавлены');
        return redirect()->route('payments.index');
    }

    public function edit(Request $request, Payment $payment)
    {
        $hotel = $request->session()->get('hotel_id');
        $payments = explode(', ', $payment->payments);
        return view('auth.payments.form', compact('payment', 'hotel', 'payments'));
    }

    public function update(Request $request, Payment $payment)
    {
        $params = [
            'hotel_id' => $request->hotel_id,
            'payments' => implode(', ', $request->payments),
        ];
        $payment->update($params);
        session()->flash('success', 'Способы оплаты обновлены');
        return redirect()->route('payments.index');
    }

}
