<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offline;
use Illuminate\Http\Request;

class OfflineController extends Controller
{
    public function index()
    {
        $offlines = Offline::all();
        return view('auth.offlines.index', compact('offlines'));
    }

    public function show($id)
    {
        $offline = Offline::where('id', $id)->firstOrFail();
        return view('auth.offlines.show', compact('offline'));
    }
}
