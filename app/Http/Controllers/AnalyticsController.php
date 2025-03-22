<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\InternationInquiry;
use Carbon\Carbon;


class AnalyticsController extends Controller
{
    public function getInquiryData(Request $request){

        $timeRange = $request->query('timeRange', 'Last 30 days');
        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            switch ($timeRange) {
                case "Today":
                    $from = Carbon::today()->startOfDay()->toDateTimeString(); // Include time
                    $to = Carbon::today()->endOfDay()->toDateTimeString(); // Include time
                    break;
                case "Last 7 days":
                    $from = Carbon::today()->subDays(6)->startOfDay()->toDateTimeString(); // Include time
                    $to = Carbon::today()->endOfDay()->toDateTimeString(); // Include time
                    break;
                case "Last 30 days":
                    $from = Carbon::now()->subDays(29)->startOfDay()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
                case "Last month":
                    $from = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
                    $to = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
            }
    
        }
        $domesticData = Inquiry::selectRaw('DATE(created_at) as date, COUNT(*) as dom_count')
        ->where('status', 2)
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $internationalData = InternationInquiry::selectRaw('DATE(created_at) as date, COUNT(*) as int_count')
        ->where('status', 2)
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $mergedData = [];
        $allDates = collect(
            array_merge(
                $domesticData->keys()->toArray(), 
                $internationalData->keys()->toArray(),
                ))->unique();

        foreach ($allDates as $date) {
            $mergedData[] = [
                'date' => $date,
                'Dom' => $domesticData[$date]->dom_count ?? 0,
                'Int' => $internationalData[$date]->int_count ?? 0,
            ];
        }     

        return response()->json(
            $mergedData
        );       
    
    }

    public function getOffersData(Request $request){

        $timeRange = $request->query('timeRange', 'Last 30 days');
        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            switch ($timeRange) {
                case "Today":
                    $from = Carbon::today()->startOfDay()->toDateTimeString(); // Include time
                    $to = Carbon::today()->endOfDay()->toDateTimeString(); // Include time
                    break;
                case "Last 7 days":
                    $from = Carbon::today()->subDays(6)->startOfDay()->toDateTimeString(); // Include time
                    $to = Carbon::today()->endOfDay()->toDateTimeString(); // Include time
                    break;
                case "Last 30 days":
                    $from = Carbon::now()->subDays(29)->startOfDay()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
                case "Last month":
                    $from = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
                    $to = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
            }
    
        }

        $offersData = Inquiry::selectRaw('DATE(created_at) as date, COUNT(*) as dom_offers_count')
        ->where('status', 1)
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $internationalOffersData = InternationInquiry::selectRaw('DATE(created_at) as date, COUNT(*) as int_offers_count')
        ->where('status', 1)
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $mergedData = [];
        $allDates = collect(
            array_merge(
                $offersData->keys()->toArray(),
                $internationalOffersData->keys()->toArray()
                ))->unique();

        foreach ($allDates as $date) {
            $mergedData[] = [
                'date' => $date,
                'DomOffers' => $offersData[$date]->dom_offers_count ?? 0,
                'IntOffers' => $internationalOffersData[$date]->int_offers_count ?? 0,

            ];
        }     

        return response()->json(
            $mergedData
        );       
    
    }


    public function getTotalInquiries(Request $request) {
        // Get total inquiries (domestic + international)
        $totalDomestic = Inquiry::where('status','2')->count();
        $totalInternational = InternationInquiry::where('status','2')->count();
        $totalInquiries = $totalDomestic + $totalInternational;
    
        // Get inquiries from last month
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();
    
        $lastMonthDomestic = Inquiry::where('status',2)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInternational = InternationInquiry::where('status',2)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInquiries = $lastMonthDomestic + $lastMonthInternational;
    

        // Get total inquiries (domestic + international)
        $totalDomesticOffers = Inquiry::where('status', 1)->count();
        $totalInternationalOffers = InternationInquiry::where('status',1)->count();
        $totalOffers = $totalDomesticOffers + $totalInternationalOffers;
    
    
        $lastMonthDomesticOffers = Inquiry::where('status',1)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInternational = InternationInquiry::where('status',1)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthOffers = $lastMonthDomestic + $lastMonthInternational;



        // Return data
        return response()->json([
            'total_inquiries' => $totalInquiries,
            'last_month_inquiries' => $lastMonthInquiries,
            'total_offers' => $totalOffers,
            'last_month_offers' => $lastMonthOffers
        ]);
    }
    
    

}
