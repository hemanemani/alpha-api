<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\InternationInquiry;
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
        ];

        $inquiryOfferDateRanges = [
            'today' => Inquiry::whereDate('created_at', $today)->where('status', '1')->count(),
            'yesterday' => Inquiry::where('created_at', '>=', now()->subDay())->where('status', '1')->count(),
            'last_7_days' => Inquiry::where('created_at', '>=', now()->subDays(7))->where('status', '1')->count(),
            'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->where('status', '1')->count(),
            'last_90_days' => Inquiry::where('created_at', '>=', now()->subDays(90))->where('status', '1')->count(),
            'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->where('status', '1')->count(),
        ];

        $inquiryCancelDateRanges = [
            'today' => Inquiry::whereDate('created_at', $today)->where('status', '0')->count(),
            'yesterday' => Inquiry::where('created_at', '>=', now()->subDay())->where('status', '0')->count(),
            'last_7_days' => Inquiry::where('created_at', '>=', now()->subDays(7))->where('status', '0')->count(),
            'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->where('status', '0')->count(),
            'last_90_days' => Inquiry::where('created_at', '>=', now()->subDays(90))->where('status', '0')->count(),
            'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->where('status', '0')->count(),
        ];

        // Fetch counts for InternationInquiry
        $interInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->count(),
        ];

        $interOfferInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->where('status', '1')->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->where('status', '1')->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->where('status', '1')->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->where('status', '1')->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->where('status', '1')->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->where('status', '1')->count(),
        ];

        $interCancelInquiryDateRanges = [
            'today' => InternationInquiry::whereDate('created_at', $today)->where('status', '0')->count(),
            'yesterday' => InternationInquiry::where('created_at', '>=', now()->subDay())->where('status', '0')->count(),
            'last_7_days' => InternationInquiry::where('created_at', '>=', now()->subDays(7))->where('status', '0')->count(),
            'last_30_days' => InternationInquiry::where('created_at', '>=', now()->subDays(30))->where('status', '0')->count(),
            'last_90_days' => InternationInquiry::where('created_at', '>=', now()->subDays(90))->where('status', '0')->count(),
            'last_365_days' => InternationInquiry::where('created_at', '>=', now()->subDays(365))->where('status', '0')->count(),
        ];

        $customInquiryDateRange = null;
        $customInterInquiryDateRange = null;

        if ($request->has(['start_date', 'end_date'])) {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $customInquiryDateRange = Inquiry::whereBetween('created_at', [$startDate, $endDate])->count();
            $customInterInquiryDateRange = InternationInquiry::whereBetween('created_at', [$startDate, $endDate])->count();
        }

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
                'topLocations' => $topLocations
            ],
        ]);

    }        
}

