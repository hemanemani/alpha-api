<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InternationalInquiryController;


Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/inquiries/download-template', [InquiryController::class, 'downloadTemplate'])->name('inquiries.downloadTemplate');
Route::get('/international-inquiries/download-template', [InternationalInquiryController::class, 'downloadTemplate'])->name('international.inquiries.downloadTemplate');

require __DIR__.'/auth.php';
