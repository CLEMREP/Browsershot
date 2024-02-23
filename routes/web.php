<?php

use Illuminate\Support\Facades\Route;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print', function () {
    Pdf::view('welcome')
        ->withBrowsershot(
            fn (Browsershot $browsershot) => $browsershot
                ->margins(0, 0, 0, 0)
                ->emulateMedia('print')
                ->showBackground()
                ->waitUntilNetworkIdle()
        )
        ->format('A4')
        ->save('test.pdf');
});
