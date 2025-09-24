<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Exely\FetchPropertyIdsJob;
use Illuminate\Support\Facades\Cache;

class ImportController extends Controller
{
    public function exelyStart()
    {
        dispatch(new FetchPropertyIdsJob())->onQueue('imports');
        return response()->json(['ok'=>true, 'message'=>'Queued', 'progressKey'=>'exely']);
    }

    public function exelyProgress()
    {
        return response()->json([
            'total' => (int) Cache::get('exely:total', 0),
            'done'  => (int) Cache::get('exely:done', 0),
        ]);
    }

}