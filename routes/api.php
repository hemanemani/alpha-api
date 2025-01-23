<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\CheckIfAdmin;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InternationalInquiryController;


Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::resource('inquiries',InquiryController::class);
    Route::get('/inquiry-approved-offers', [InquiryController::class, 'approved_offers'])->name('inquiry.approved.offers');
    Route::get('/inquiry-cancellation-offers', [InquiryController::class, 'cancellation_offers'])->name('inquiry.cancellation.offers');
    Route::patch('/inquiries/{id}/update-inquiry-status', [InquiryController::class, 'updateInquiryStatus'])->name('inquiries.updateInquiryStatus');
    Route::post('/inquiries/bulk-upload', [InquiryController::class, 'bulkUpload'])->name('inquiries.bulkUpload');

    Route::resource('international_inquiries',InternationalInquiryController::class);
    Route::get('/inquiry-approved-international-offers', [InternationalInquiryController::class, 'approved_offers'])->name('inquiry.international.approved.offers');
    Route::get('/inquiry-cancellation-international-offers', [InternationalInquiryController::class, 'cancellation_offers'])->name('inquiry.international.cancellation.offers');
    Route::patch('/international-inquiries/{id}/update-international-inquiry-status', [InternationalInquiryController::class, 'updateInternationInquiryStatus'])->name('inquiries.updateInternationInquiryStatus');
    Route::post('/international-inquiries/bulk-upload', [InternationalInquiryController::class, 'bulkUpload'])->name('international.inquiries.bulkUpload');
});



Route::middleware(['auth:sanctum', CheckIfAdmin::class])->group(function () {
    Route::resource('users', UserController::class);
});