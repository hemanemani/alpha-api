<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\InternationInquiry;
use App\Models\Offer;
use App\Models\InternationalOffer;
use App\Models\BlockedInquiry;
use App\Models\BlockedInternationalInquiry;
use App\Models\BlockedOffer;
use App\Models\BlockedInternationalOffer;
use App\Models\UploadInquiry;
use App\Models\UploadInternationalInquiry;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\InternationalOrder;
use App\Models\BlockedOrder;
use App\Models\BlockedInternationalOrder;
use App\Models\InternationalOrderSeller;
use App\Models\OrderSeller;

class DashboardController extends Controller
{
    public function refresh_all(Request $request){

        $topLocations = Inquiry::select('location', DB::raw('count(*) as count'))
        ->groupBy('location')
        ->orderByDesc('count')
        ->limit(5)
        ->get();

        $topInternationalLocations = InternationInquiry::select('location', DB::raw('count(*) as count'))
        ->groupBy('location')
        ->orderByDesc('count')
        ->limit(5)
        ->get();

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();


        // Fetch counts for Inquiry
        $inquiryDateRanges = [
            'today' => Inquiry::whereDate('inquiry_date', $today)->count(),
            'yesterday' => Inquiry::whereDate('inquiry_date', $yesterday)->count(),
        ];

        $inquiryOfferDateRanges = [
            'today' => Inquiry::whereDate('inquiry_date', $today)->where('status', '1')->count(),
            'yesterday' => Inquiry::whereDate('inquiry_date', $yesterday)->where('status', '1')->count(),
        ];

        $inquiryCancelDateRanges = [
            'today' => Inquiry::whereDate('inquiry_date', $today)->where('status', '0')->count(),
            'yesterday' => Inquiry::whereDate('inquiry_date', $yesterday)->where('status', '0')->count(),
        ];

        $orderDateRanges = [
            'today' => Inquiry::whereDate('inquiry_date', $today)->where('offers_status', '1')->count(),
            'yesterday' => Inquiry::whereDate('inquiry_date', $yesterday)->where('offers_status', '1')->count(),
        ];


        // Fetch counts for InternationInquiry
        $interInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('inquiry_date', $today)->count(),
            'yesterday' => InternationInquiry::whereDate('inquiry_date', $yesterday)->count(),
        ];

        $interOfferInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('inquiry_date', $today)->where('status', '1')->count(),
            'yesterday' => InternationInquiry::whereDate('inquiry_date', $yesterday)->where('status', '1')->count(),
        ];

        $interCancelInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('inquiry_date', $today)->where('status', '0')->count(),
            'yesterday' => InternationInquiry::whereDate('inquiry_date', $yesterday)->where('status', '0')->count(),
        ];

        $interOrderDateRanges = [
            'today' => InternationInquiry::whereDate('inquiry_date', $today)->where('offers_status', '1')->count(),
            'yesterday' => InternationInquiry::whereDate('inquiry_date', $yesterday)->where('offers_status', '1')->count(),
        ];


        $totalInquiriesCount = Inquiry::all()->count();
        $totalInternationalCount = InternationInquiry::all()->count();

        $inquiryThirdContentNullCount = Inquiry::whereNull('third_contact_date')->count();
        $inquiryThirdContentNotNullCount = Inquiry::whereNotNull('third_contact_date')->count();


        $inquiryCount = Inquiry::where('status','2')->count();
        $interInquiryCount = InternationInquiry::where('status','2')->count();

        $inquiryOffersCount = Inquiry::where('status','1')->count();
        $interInquiryOffersCount = InternationInquiry::where('status','1')->count();

        $OrdersCount = Order::where('status','2')->count();
        $interOrdersCount = InternationalOrder::where('status','2')->count();

        $inquiryCancelCount = Inquiry::where('status','0')->count();
        $interInquiryCancelCount = InternationInquiry::where('status','0')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'inquiry' => [
                    'name' => 'inquiry',
                    'count' => $inquiryCount,
                    'offers' => $inquiryOffersCount,
                    'cancellations' => $inquiryCancelCount,
                    'dateRanges' => $inquiryDateRanges,
                    'offerDateRanges' => $inquiryOfferDateRanges,
                    'cancelDateRanges' => $inquiryCancelDateRanges,
                    'orders' => $OrdersCount,
                    'orderDateRanges' => $orderDateRanges
                ],
                'interInquiry' => [
                    'name' => 'interInquiry',
                    'count' => $interInquiryCount,
                    'offers' => $interInquiryOffersCount,
                    'cancellations' => $interInquiryCancelCount,
                    'dateRanges' => $interInquiryDateRanges,
                    'cancelDateRanges' => $interCancelInquiryDateRanges,
                    'orders' => $interOrdersCount,
                    'interOrderDateRanges' => $interOrderDateRanges
                ],
                'topLocations' => $topLocations,
                'topInternationalLocations' => $topInternationalLocations,
                'totalInquiriesCount'=> $totalInquiriesCount,
                'totalInternationalCount'=> $totalInternationalCount,
                'inquiryThirdContentNullCount'=> $inquiryThirdContentNullCount ?? 0,
                'inquiryThirdContentNotNullCount' => $inquiryThirdContentNotNullCount ?? 0
            ],
        ]);

    } 
    public function deleteAllData(Request $request)
    {
        try {
            $hasData =
            Inquiry::exists() ||
            InternationInquiry::exists() ||
            Offer::exists() ||
            InternationalOffer::exists() ||
            BlockedInquiry::exists() ||
            BlockedInternationalInquiry::exists() ||
            BlockedOffer::exists() ||
            BlockedInternationalOffer::exists() ||
            UploadInquiry::exists() ||
            UploadInternationalInquiry::exists() ||
            Order::exists() ||
            InternationalOrder :: exists() ||
            BlockedOrder :: exists() ||
            BlockedInternationalOrder :: exists() ||
            OrderSeller::exists() ||
            InternationalOrderSeller::exists();

        if (!$hasData) {
            return response()->json(['message' => 'No data to delete.'], 200);
        }

            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Delete related records
            Inquiry::truncate();
            InternationInquiry::truncate();
            Offer::truncate();
            InternationalOffer::truncate();
            BlockedInquiry::truncate();
            BlockedInternationalInquiry::truncate();
            BlockedOffer::truncate();
            BlockedInternationalOffer::truncate();
            UploadInquiry::truncate();
            UploadInternationalInquiry::truncate(); 
            Order::truncate();
            InternationalOrder::truncate();
            BlockedOrder::truncate();
            BlockedInternationalOrder::truncate();  
            OrderSeller::truncate();
            InternationalOrderSeller:: truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();
            return response()->json(['message' => 'All dashboard data deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'All dashboard data deleted successfully',
                'error' => $e->getMessage()
            ], 500);
        }
    }
       
}

