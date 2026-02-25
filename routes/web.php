<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\Public\FormController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware('install.guard')->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->name('database');
    Route::get('/app', [InstallController::class, 'appConfig'])->name('app');
    Route::post('/app', [InstallController::class, 'saveAppConfig'])->name('app.save');
    Route::get('/system', [InstallController::class, 'systemInstall'])->name('system');
    Route::post('/admin', [InstallController::class, 'createAdmin'])->name('admin');
    Route::get('/done', [InstallController::class, 'done'])->name('done');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
});

Route::get('/', HomeController::class)->name('home');
Route::post('/contact-us', [FormController::class, 'contact'])->middleware('throttle:forms')->name('contact.submit');

Route::prefix('{locale}')->middleware('set.locale')->whereIn('locale', ['en', 'fr', 'es'])->group(function () {
    Route::get('/', HomeController::class);
});

Route::get('/our-properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/city/{citySlug}', [PropertyController::class, 'city'])->name('properties.city');
Route::post('/properties/{propertyId}/inquiry', [FormController::class, 'inquiry'])->middleware('throttle:forms')->name('properties.inquiry');

Route::view('/our-services', 'public.placeholder')->name('services');
Route::view('/rd', 'public.placeholder')->name('rd');
Route::view('/faq', 'public.placeholder')->name('faq');
Route::view('/blog', 'public.placeholder')->name('blog.index');
Route::view('/contact-us', 'public.contact')->name('contact');
Route::view('/terms-and-conditions', 'public.placeholder')->name('terms');
