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

class DashboardController extends Controller
{
    public function refresh_all(Request $request){

        $topLocations = Inquiry::select('location', DB::raw('count(*) as count'))
        ->groupBy('location')
        ->orderByDesc('count')
        ->limit(5)
        ->get();

        $today = now()->startOfDay();
        // Fetch counts for Inquiry
        $inquiryDateRanges = [
            'today' => Inquiry::whereDate('created_at', $today)->count(),
            'yesterday' => Inquiry::where('created_at', '>=', now()->subDay())->count(),
            'last_7_days' => Inquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->count(),
            'last_90_days' => Inquiry::where('created_at', '>=', now()->subDays(90))->count(),
            'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->count(),
            'this_month' => Inquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count(),

        ];

        $inquiryOfferDateRanges = [
            'today' => Inquiry::whereDate('created_at', $today)->where('status', '1')->count(),
            'yesterday' => Inquiry::where('created_at', '>=', now()->subDay())->where('status', '1')->count(),
            'last_7_days' => Inquiry::where('created_at', '>=', now()->subDays(7))->where('status', '1')->count(),
            'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->where('status', '1')->count(),
            'last_90_days' => Inquiry::where('created_at', '>=', now()->subDays(90))->where('status', '1')->count(),
            'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->where('status', '1')->count(),
            'this_month' => Inquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->where('status', '1')
            ->count(),

        ];

        $inquiryCancelDateRanges = [
            'today' => Inquiry::whereDate('created_at', $today)->where('status', '0')->count(),
            'yesterday' => Inquiry::where('created_at', '>=', now()->subDay())->where('status', '0')->count(),
            'last_7_days' => Inquiry::where('created_at', '>=', now()->subDays(7))->where('status', '0')->count(),
            'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->where('status', '0')->count(),
            'last_90_days' => Inquiry::where('created_at', '>=', now()->subDays(90))->where('status', '0')->count(),
            'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->where('status', '0')->count(),
            'this_month' => Inquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->where('status', '0')
            ->count(),
        ];

        // Fetch counts for InternationInquiry
        $interInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->count(),
            'this_month' => InternationInquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count(),
        ];

        $interOfferInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->where('status', '1')->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->where('status', '1')->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->where('status', '1')->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->where('status', '1')->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->where('status', '1')->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->where('status', '1')->count(),
            'this_month' => InternationInquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->where('status', '1')
            ->count(),
        ];

        $interCancelInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->where('status', '0')->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->where('status', '0')->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->where('status', '0')->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->where('status', '0')->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->where('status', '0')->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->where('status', '0')->count(),
            'this_month' => InternationInquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)->where('status', '0')
            ->count(),
        ];

        $customInquiryDateRange = null;
        $customInterInquiryDateRange = null;

        if ($request->has(['start_date', 'end_date'])) {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $customInquiryDateRange = Inquiry::whereBetween('created_at', [$startDate, $endDate])->count();
            $customInterInquiryDateRange = InternationInquiry::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        $totalInquiriesCount = Inquiry::all()->count();
        $totalInternationalCount = InternationInquiry::all()->count();

        $inquiryThirdContentNullCount = Inquiry::whereNull('third_contact_date')->count();
        $inquiryThirdContentNotNullCount = Inquiry::whereNotNull('third_contact_date')->count();


        $inquiryCount = Inquiry::where('status','2')->count();
        $interInquiryCount = InternationInquiry::where('status','2')->count();

        $inquiryOffersCount = Inquiry::where('status','1')->count();
        $interInquiryOffersCount = InternationInquiry::where('status','1')->count();

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
                    'customDateRange' => $customInquiryDateRange
                ],
                'interInquiry' => [
                    'name' => 'interInquiry',
                    'count' => $interInquiryCount,
                    'offers' => $interInquiryOffersCount,
                    'cancellations' => $interInquiryCancelCount,
                    'dateRanges' => $interInquiryDateRanges,
                    'cancelDateRanges' => $interCancelInquiryDateRanges,
                    'customDateRange' => $customInterInquiryDateRange
                ],
                'topLocations' => $topLocations,
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
            UploadInternationalInquiry::exists();

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
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();
            return response()->json(['message' => 'All dashboard data deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
       
}

