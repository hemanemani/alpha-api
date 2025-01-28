<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\InternationInquiry;

class DashboardController extends Controller
{
    public function refresh_all(){
        try {
            $inquiryCount = Inquiry::count();
            $interInquiryCount = InternationInquiry::count();
            return response()->json([
                'success' => true,
                'data' =>[
                    'inquiry' => [
                        'name' => 'inquiry',
                        'count' => $inquiryCount
                    ],
                    'interInquiry' => [
                        'name' => 'interinquiry',
                        'count' => $interInquiryCount
                    ]
                ]
            
                ]);
            } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to get data counts',
                        'error' => $e->getMessage(),
                    ], 500);
            
            }
    }
    public function inquiry_calender(Request $request)
    {
        try {
            $today = now()->startOfDay();
            $dateRanges = [
                'today' => Inquiry::whereDate('created_at', $today)->count(),
                'last_30_days' => Inquiry::where('created_at', '>=', now()->subDays(30))->count(),
                'last_60_days' => Inquiry::where('created_at', '>=', now()->subDays(60))->count(),
                'last_365_days' => Inquiry::where('created_at', '>=', now()->subDays(365))->count(),
            ];

            if ($request->has(['start_date', 'end_date'])) {
                $startDate = $request->query('start_date');
                $endDate = $request->query('end_date');

                $dateRanges['custom_date_range'] = Inquiry::whereBetween('created_at', [$startDate, $endDate])->count();
            }

            return response()->json([
                'success' => true,
                'data' => $dateRanges,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inquiries',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



        
}

