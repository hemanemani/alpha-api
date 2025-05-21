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
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InternationalOrderController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AudienceController;

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

    Route::get('/refresh-all', [DashboardController::class, 'refresh_all']);

    Route::get('/inquiries/next-number', [InquiryController::class, 'getNextInquiryNumber']);
    Route::get('/international_inquiries/next-number', [InternationalInquiryController::class, 'getNextInternationalInquiryNumber']);

    Route::get('/orders/next-number', [OrderController::class, 'getNextOrderNumber']);
    Route::get('/international-orders/next-number', [InternationalOrderController::class, 'getNextInternationalOrderNumber']);


    Route::resource('inquiries',InquiryController::class);

    Route::get('/all-inquiries',[InquiryController::class,'total_index']);



    Route::get('/inquiry-approved-offers', [InquiryController::class, 'approved_offers'])->name('inquiry.approved.offers');
    Route::get('/inquiry-cancellation-offers', [InquiryController::class, 'cancellation_offers'])->name('inquiry.cancellation.offers');
    Route::patch('/inquiries/{id}/update-inquiry-status', [InquiryController::class, 'updateInquiryStatus'])->name('inquiries.updateInquiryStatus');
    Route::get('/domestic-template-download', [InquiryController::class, 'downloadTemplate'])->name('domestic.download.template');
    Route::post('/inquiries/bulk-upload', [InquiryController::class, 'bulkUpload'])->name('inquiries.bulkUpload');
    Route::get('/bulk-domestic-data', [InquiryController::class, 'bulkUploadData'])->name('inquiries.bulkUpload.data');
    Route::delete('/bulk-domestic-data/{id}', [InquiryController::class, 'uploadDestroy']);
    Route::post('/block-inquiry/{id}', [InquiryController::class, 'blockInquiry']);


    Route::get('/all-international-inquiries',[InternationalInquiryController::class,'total_index']);

    Route::resource('international_inquiries',InternationalInquiryController::class);
    Route::get('/inquiry-approved-international-offers', [InternationalInquiryController::class, 'approved_offers'])->name('inquiry.international.approved.offers');
    Route::get('/inquiry-cancellation-international-offers', [InternationalInquiryController::class, 'cancellation_offers'])->name('inquiry.international.cancellation.offers');
    Route::patch('/international-inquiries/{id}/update-international-inquiry-status', [InternationalInquiryController::class, 'updateInternationInquiryStatus'])->name('inquiries.updateInternationInquiryStatus');
    Route::get('/international-template-download', [InternationalInquiryController::class, 'downloadTemplate'])->name('international.download.template');
    Route::post('/international-inquiries/bulk-upload', [InternationalInquiryController::class, 'bulkUpload'])->name('international.inquiries.bulkUpload');
    Route::get('/bulk-international-data', [InternationalInquiryController::class, 'bulkUploadData'])->name('international.inquiries.bulkUpload.data');
    Route::delete('/bulk-international-data/{id}', [InternationalInquiryController::class, 'uploadDestroy']);
    Route::post('/block-international-inquiry/{id}', [InternationalInquiryController::class, 'blockInternationalInquiry']);

    //offers & cancellations

    Route::get('/inquiries/{id}/with-offers', [InquiryController::class,'getInquiryWithOffers']);
    Route::get('/international-inquiries/{id}/with-offers', [InternationalInquiryController::class,'getInternationalInquiryWithOffers']);

    Route::get('/offer-domestic-cancellations', [InquiryController::class, 'offerDomesticCancellations'])->name('offer.domestic.cancellations');
    Route::get('/offer-international-cancellations', [InternationalInquiryController::class, 'offerInternationalCancellations'])->name('offer.international.cancellations');

    Route::post('/block-offer/{id}', [InquiryController::class, 'blockOffer']);
    Route::post('/block-international-offer/{id}', [InternationalInquiryController::class, 'blockInternationalOffer']);

    Route::get('/analytics/inquiries', [AnalyticsController::class, 'getInquiryData']);
    Route::get('/analytics/offers', [AnalyticsController::class, 'getOffersData']);
    Route::get('/analytics/ads', [AnalyticsController::class, 'getAdsData']);
    Route::get('/analytics/orders', [AnalyticsController::class, 'getOrdersData']);



    Route::get('/analytics/total-inquiries', [AnalyticsController::class, 'getTotalInquiries']);
    

    Route::delete('/dashboard/delete-all', [DashboardController::class, 'deleteAllData']);


    Route::resource('sellers',SellerController::class);
    Route::get('/seller-details', [SellerController::class, 'getSellers']);


    Route::resource('products',ProductController::class);

    



    Route::resource('orders', OrderController::class);
    Route::get('orders/by-offer/{id}', [OrderController::class, 'showByOrderId']);
    Route::post('orders/generate-invoice-pdf', [OrderController::class, 'generatePDF']);

    Route::get('/order-domestic-cancellations', [InquiryController::class, 'orderDomesticCancellations'])->name('order.domestic.cancellations');


    Route::resource('international-orders', InternationalOrderController::class);
    Route::get('international-orders/by-offer/{id}', [InternationalOrderController::class, 'showByOrderId']);
    Route::post('international-orders/generate-invoice-pdf', [InternationalOrderController::class, 'generatePDF']);

    Route::get('/order-international-cancellations', [InternationalInquiryController::class, 'orderInternationalCancellations'])->name('order.international.cancellations');

    Route::post('/block-order/{id}', [InquiryController::class, 'blockOrder']);
    Route::post('/block-international-order/{id}', [InternationalInquiryController::class, 'blockInternationalOrder']);

    Route::resource('ads', AdController::class);
    Route::get('/international-ads',[AdController::class,'international_index']);


    Route::get('/audiences', [AudienceController::class, 'index']);
    Route::post('/audiences', [AudienceController::class, 'store']);
    Route::delete('/audiences/{id}', [AudienceController::class, 'destroy']);


});


Route::middleware(['auth:sanctum', CheckIfAdmin::class])->group(function () {
    Route::resource('users', UserController::class);
});