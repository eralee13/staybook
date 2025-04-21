<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;

class AllBillsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:finance-bill', ['only' => ['index']]);
    }

    public function index()
    {
        $bills = Bill::latest()->paginate(30);
        return view('auth.bills.finance.index', compact('bills'));
    }
}
