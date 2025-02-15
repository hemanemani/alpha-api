<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\CheckIfAdmin;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InternationalInquiryController;
use App\Http\Controllers\DashboardController;
use Laravel\Sanctum\Sanctum;
use App\Http\Middleware\CheckAccessLevel;
use App\Http\Controllers\OfferController;


Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token fetched successfully']);
});

Route::middleware(['auth:sanctum'])->get('/user-access', function (Request $request) {
    return response()->json(
        ['access_level' => $request->user()->access_level,
        'allowed_pages' => $request->user()->allowed_pages ?? [] ]
    );
});


Route::middleware(['auth:sanctum'])->group(function () {

    

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::post('/update-status/{id}',[UserController::class,'updateStatus']);

    Route::get('/refresh', [DashboardController::class, 'refresh_all']);


    Route::resource('inquiries',InquiryController::class);
    Route::get('/inquiry-approved-offers', [InquiryController::class, 'approved_offers'])->name('inquiry.approved.offers');
    Route::get('/inquiry-cancellation-offers', [InquiryController::class, 'cancellation_offers'])->name('inquiry.cancellation.offers');
    Route::patch('/inquiries/{id}/update-inquiry-status', [InquiryController::class, 'updateInquiryStatus'])->name('inquiries.updateInquiryStatus');
    Route::get('/domestic-template-download', [InquiryController::class, 'downloadTemplate'])->name('domestic.download.template');
    Route::post('/inquiries/bulk-upload', [InquiryController::class, 'bulkUpload'])->name('inquiries.bulkUpload');
    Route::get('/bulk-domestic-data', [InquiryController::class, 'bulkUploadData'])->name('inquiries.bulkUpload.data');
    Route::delete('/bulk-domestic-data/{id}', [InquiryController::class, 'uploadDestroy']);
    Route::post('/block-inquiry/{id}', [InquiryController::class, 'blockInquiry']);


    Route::resource('international_inquiries',InternationalInquiryController::class);
    Route::get('/inquiry-approved-international-offers', [InternationalInquiryController::class, 'approved_offers'])->name('inquiry.international.approved.offers');
    Route::get('/inquiry-cancellation-international-offers', [InternationalInquiryController::class, 'cancellation_offers'])->name('inquiry.international.cancellation.offers');
    Route::patch('/international-inquiries/{id}/update-international-inquiry-status', [InternationalInquiryController::class, 'updateInternationInquiryStatus'])->name('inquiries.updateInternationInquiryStatus');
    Route::get('/international-template-download', [InternationalInquiryController::class, 'downloadTemplate'])->name('international.download.template');
    Route::post('/international-inquiries/bulk-upload', [InternationalInquiryController::class, 'bulkUpload'])->name('international.inquiries.bulkUpload');

});


Route::middleware(['auth:sanctum', CheckIfAdmin::class])->group(function () {
    Route::resource('users', UserController::class);
});