<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InternationalInquiryController;
use App\Http\Middleware\CheckIfAdmin;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


// Route::get('/', function () {
//     return view('auth/login');
// });


Route::get('/inquiries/download-template', [InquiryController::class, 'downloadTemplate'])->name('inquiries.downloadTemplate');
Route::get('/international-inquiries/download-template', [InternationalInquiryController::class, 'downloadTemplate'])->name('international.inquiries.downloadTemplate');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('inquiries',InquiryController::class);
    Route::patch('/inquiries/{id}/update-inquiry-status', [InquiryController::class, 'updateInquiryStatus'])->name('inquiries.updateInquiryStatus');
    Route::get('/inquiry-approved-offers', [InquiryController::class, 'approved_offers'])->name('inquiry.approved.offers');
    Route::get('/inquiry-cancellation-offers', [InquiryController::class, 'cancellation_offers'])->name('inquiry.cancellation.offers');
    Route::post('/inquiries/bulk-upload', [InquiryController::class, 'bulkUpload'])->name('inquiries.bulkUpload');



    Route::resource('international_inquiries',InternationalInquiryController::class);
    Route::patch('/international-inquiries/{id}/update-international-inquiry-status', [InternationalInquiryController::class, 'updateInternationInquiryStatus'])->name('inquiries.updateInternationInquiryStatus');
    Route::get('/inquiry-approved-international-offers', [InternationalInquiryController::class, 'approved_offers'])->name('inquiry.international.approved.offers');
    Route::get('/inquiry-cancellation-international-offers', [InternationalInquiryController::class, 'cancellation_offers'])->name('inquiry.international.cancellation.offers');
    Route::post('/international-inquiries/bulk-upload', [InternationalInquiryController::class, 'bulkUpload'])->name('international.inquiries.bulkUpload');

});


Route::middleware([CheckIfAdmin::class, 'auth'])->group(function () {
    Route::resource('users', UserController::class);
});
require __DIR__.'/auth.php';
