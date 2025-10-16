<?php

use Illuminate\Support\Facades\Route;

Route::get('docs/openapi.json', function () {
    return redirect('/docs/api.json', 302);
})->name('scramble.docs.document.alias');
