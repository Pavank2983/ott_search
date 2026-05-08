<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/search-ui');
});

Route::view(
    '/search-ui',
    'search.index'
);