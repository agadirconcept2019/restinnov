<?php

use Illuminate\Support\Facades\Route;

Route::get('/core-health', fn () => response()->json(['ok' => true]))->name('core.health');
