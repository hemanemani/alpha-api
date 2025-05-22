<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\InternationInquiry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Offer;
use App\Models\InternationalOffer;
use App\Models\Ad;
use App\Models\InternationalAd;
use App\Models\Order;
use App\Models\InternationalOrder;
use App\Models\OrderSeller;
use Illuminate\Support\Facades\DB;
use App\Models\InternationalOrderSeller;

class AnalyticsController extends Controller
{
    public function getInquiryData(Request $request){

        $timeRange = $request->query('timeRange', 'Last 30 days');
        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            switch ($timeRange) {
                case "Today":
                    $from = Carbon::today()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 7 days":
                    $from = Carbon::today()->subDays(6)->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 30 days":
                    $from = Carbon::now()->subDays(30)->toDateString();
                    $to = Carbon::now()->toDateString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 6 months":
                    $from = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
            }
            
    
        }
        $domesticData = Inquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as dom_count')
        ->where('status', 2)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
        ->get()
        ->keyBy('date');

        $internationalData = InternationInquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as int_count')
        ->where('status', 2)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
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
                    $from = Carbon::today()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 7 days":
                    $from = Carbon::today()->subDays(6)->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 30 days":
                    $from = Carbon::now()->subDays(30)->toDateString();
                    $to = Carbon::now()->toDateString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 6 months":
                    $from = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
            }
            
    
        }

        $offersData = Inquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as dom_offers_count')
        ->where('status', 1)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
        ->get()
        ->keyBy('date');

        $internationalOffersData = InternationInquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as int_offers_count')
        ->where('status', 1)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
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

    public function getOrdersData(Request $request){

        $timeRange = $request->query('timeRange', 'Last 30 days');
        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            switch ($timeRange) {
                case "Today":
                    $from = Carbon::today()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 7 days":
                    $from = Carbon::today()->subDays(6)->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 30 days":
                    $from = Carbon::now()->subDays(30)->toDateString();
                    $to = Carbon::now()->toDateString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
                case "Last 6 months":
                    $from = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
                    $to = Carbon::today()->toDateString();
                    break;
            }
            
    
        }

        $ordersData = Inquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as dom_orders_count')
        ->where('offers_status', 1)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
        ->get()
        ->keyBy('date');

        $internationalOrdersData = InternationInquiry::selectRaw('DATE(inquiry_date) as date, COUNT(*) as int_orders_count')
        ->where('offers_status', 1)
        ->whereBetween('inquiry_date', [$from, $to])
        ->groupBy('inquiry_date')
        ->orderBy('inquiry_date')
        ->get()
        ->keyBy('date');

        $mergedData = [];
        $allDates = collect(
            array_merge(
                $ordersData->keys()->toArray(),
                $internationalOrdersData->keys()->toArray()
                ))->unique();

        foreach ($allDates as $date) {
            $mergedData[] = [
                'date' => $date,
                'DomOrders' => $ordersData[$date]->dom_orders_count ?? 0,
                'IntOrders' => $internationalOrdersData[$date]->int_orders_count ?? 0,

            ];
        }     

        return response()->json(
            $mergedData
        );       
    
    }

    public function getAdsData(Request $request){

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
                    $from = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();
                    $to = Carbon::now()->endOfDay()->toDateTimeString();
                    break;
                case "Last 3 months":
                    $from = Carbon::now()->subMonths(3)->startOfMonth()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
                case "Last 6 months":
                    $from = Carbon::now()->subMonths(6)->startOfMonth()->toDateTimeString();
                    $to = Carbon::today()->endOfDay()->toDateTimeString();
                    break;
            }
    
        }
        $domesticAdData = Ad::selectRaw('DATE(date_published) as date, COUNT(*) as dom_ad_count')
        ->whereBetween('date_published', [$from, $to])
        ->groupBy('date_published')
        ->orderBy('date_published')
        ->get()
        ->keyBy('date');

        $internationalAdData = InternationalAd::selectRaw('DATE(date_published) as date, COUNT(*) as int_ad_count')
        ->whereBetween('date_published', [$from, $to])
        ->groupBy('date_published')
        ->orderBy('date_published')
        ->get()
        ->keyBy('date');

        $mergedData = [];
        $allDates = collect(
            array_merge(
                $domesticAdData->keys()->toArray(), 
                $internationalAdData->keys()->toArray(),
                ))->unique();

        foreach ($allDates as $date) {
            $mergedData[] = [
                'date' => $date,
                'DomAd' => $domesticAdData[$date]->dom_ad_count ?? 0,
                'IntAd' => $internationalAdData[$date]->int_ad_count ?? 0,
            ];
        }     

        return response()->json(
            $mergedData
        );       
    
    }


    public function getTotalInquiries(Request $request) {

        $totalInquiriesCount = Inquiry::all()->count();
        $totalInternationalCount = InternationInquiry::all()->count();

        $inquiryThirdContentNullCount = Inquiry::whereNull('third_contact_date')->count();
        $inquiryThirdContentNotNullCount = Inquiry::whereNotNull('third_contact_date')->count();


        $internationalInquiryThirdContentNullCount = InternationInquiry::whereNull('third_contact_date')->count();
        $internationalInquiryThirdContentNotNullCount = InternationInquiry::whereNotNull('third_contact_date')->count();


        // Get total inquiries (domestic + international)
        $totalDomestic = Inquiry::where('status','2')->count();
        $totalInternational = InternationInquiry::where('status','2')->count();
        $totalInquiries = $totalDomestic + $totalInternational;
    
        // Get inquiries from last month
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();


         $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
         $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

    
        $lastMonthDomestic = Inquiry::where('status',2)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInternational = InternationInquiry::where('status',2)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInquiries = $lastMonthDomestic + $lastMonthInternational;
    

        // Get total inquiries (domestic + international)
        
        $totalDomesticCancellations = Inquiry::where('status', 0)->count();
        $totalInternationalCancellations = InternationInquiry::where('status',0)->count();
    
        $lastMonthDomesticOffers = Inquiry::where('status',1)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthInternational = InternationInquiry::where('status',1)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthOffers = $lastMonthDomestic + $lastMonthInternational;

        $thisMonthtotalInquiries = Inquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $thisMonthtotalInternationalInquiries = InternationInquiry::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();


        
        //average difference between inquiry date and first contact date

        $differenctOfInquiryAndFirstContactDate = Inquiry::whereNotNull('first_contact_date')->get();

        $totalIFCDDays = 0;
        $IFCDcount = 0;
        
        foreach ($differenctOfInquiryAndFirstContactDate as $inquiry) {
            $inquiryDate = Carbon::parse($inquiry->inquiry_date);
            $firstContactDate = Carbon::parse($inquiry->first_contact_date);
            $IFCDdiff = $inquiryDate->diffInDays($firstContactDate);
            
            $totalIFCDDays += $IFCDdiff;
            $IFCDcount++;
        }
        
        $averageInqFCD = $IFCDcount > 0 ? $totalIFCDDays / $IFCDcount : 0;

        //average difference between inquiry date and third contact date

        $differenctOfInquiryAndThirdContactDate = Inquiry::whereNotNull('third_contact_date')->get();

        $totalITCDDays = 0;
        $ITDCcount = 0;
        
        foreach ($differenctOfInquiryAndThirdContactDate as $inquiry) {
            $inquiryDate = Carbon::parse($inquiry->inquiry_date);
            $thirdContactDate = Carbon::parse($inquiry->third_contact_date);
            $ITDCdiff = $inquiryDate->diffInDays($thirdContactDate);
            
            $totalITCDDays += $ITDCdiff;
            $ITDCcount++;
        }
        
        $averageInqTCD = $ITDCcount > 0 ? $totalITCDDays / $ITDCcount : 0;



        //average difference between inquiry date and first contact date

        $differenctOfInternationalInquiryAndFirstContactDate = InternationInquiry::whereNotNull('first_contact_date')->get();

        $totalIIFCDDays = 0;
        $IIFCDcount = 0;
        
        foreach ($differenctOfInternationalInquiryAndFirstContactDate as $inquiry) {
            $inquiryDate = Carbon::parse($inquiry->inquiry_date);
            $firstContactDate = Carbon::parse($inquiry->first_contact_date);
            $IIFCDdiff = $inquiryDate->diffInDays($firstContactDate);
            
            $totalIIFCDDays += $IIFCDdiff;
            $IIFCDcount++;
        }
        
        $averageInternationalInqFCD = $IIFCDcount > 0 ? $totalIIFCDDays / $IIFCDcount : 0;

        //average difference between inquiry date and third contact date

        $differenctOfInternationalInquiryAndThirdContactDate = InternationInquiry::whereNotNull('third_contact_date')->get();

        $totalIITCDDays = 0;
        $IITDCcount = 0;
        
        foreach ($differenctOfInternationalInquiryAndThirdContactDate as $inquiry) {
            $inquiryDate = Carbon::parse($inquiry->inquiry_date);
            $thirdContactDate = Carbon::parse($inquiry->third_contact_date);
            $IITDCdiff = $inquiryDate->diffInDays($thirdContactDate);
            
            $totalIITCDDays += $IITDCdiff;
            $IITDCcount++;
        }
        
        $averageInternationalInqTCD = $IITDCcount > 0 ? $totalIITCDDays / $IITDCcount : 0;
        

        /******************************* Fetch all category strings from inquiries ***********************************/

        $categoriesList = Inquiry::whereNotNull('product_categories')->pluck('product_categories');

        $inquiryCountsPerCategory = [];

        // Go through each inquiry, only once per inquiry
        foreach ($categoriesList as $categoryString) {
            $categories = array_unique(array_map('trim', explode(',', $categoryString)));
            foreach ($categories as $category) {
                $inquiryCountsPerCategory[$category] = ($inquiryCountsPerCategory[$category] ?? 0) + 1;
            }
        }

        // Sort by inquiry count (descending)
        arsort($inquiryCountsPerCategory);

        // Take top 5 categories by inquiry count
        $top5CategoriesWithCounts = [];

        foreach (array_slice($inquiryCountsPerCategory, 0, 5, true) as $category => $count) {
            $top5CategoriesWithCounts[] = [
                'category' => $category,
                'inquiries' => $count,
            ];
        }

        // international

        $internationalCategoriesList = InternationInquiry::whereNotNull('product_categories')->pluck('product_categories');

        $internationalInquiryCountsPerCategory = [];

        // Go through each inquiry, only once per inquiry
        foreach ($internationalCategoriesList as $categoryString) {
            $categories = array_unique(array_map('trim', explode(',', $categoryString)));
            foreach ($categories as $category) {
                $internationalInquiryCountsPerCategory[$category] = ($internationalInquiryCountsPerCategory[$category] ?? 0) + 1;
            }
        }

        // Sort by inquiry count (descending)
        arsort($internationalInquiryCountsPerCategory);

        // Take top 5 categories by inquiry count
        $top5InternationalCategoriesWithCounts = [];

        foreach (array_slice($internationalInquiryCountsPerCategory, 0, 5, true) as $category => $count) {
            $top5InternationalCategoriesWithCounts[] = [
                'category' => $category,
                'inquiries' => $count,
            ];
        }

        


        /******************************* Fetch all products strings from inquiries ***********************************/

        $inquiriesWithThirdContact = Inquiry::whereNotNull('specific_product')->pluck('specific_product');

        //Count how many times each product appears
        $productCounts = [];
        foreach ($inquiriesWithThirdContact as $productString) {
            $products = array_map('trim', explode(',', $productString));
            foreach ($products as $product) {
                if ($product !== '') {
                    $productCounts[$product] = ($productCounts[$product] ?? 0) + 1;
                }
            }
        }
        
        arsort($productCounts);
        $top5SpecificProductsWithCounts = [];
        foreach (array_slice($productCounts, 0, 5, true) as $product => $count) {
            $top5SpecificProductsWithCounts[] = [
                'product' => $product,
                'count' => $count,
            ];
        }

        // international

        $internationalInquiriesWithThirdContact = InternationInquiry::whereNotNull('third_contact_date')
        ->pluck('specific_product');

        //Count how many times each product appeainternationalPs
        $internationalProductCounts = [];
        foreach ($internationalInquiriesWithThirdContact as $productString) {
            $products = array_map('trim', explode(',', $productString));
            foreach ($products as $product) {
                if ($product !== '') {
                    $internationalProductCounts[$product] = ($internationalProductCounts[$product] ?? 0) + 1;
                }
            }
        }
        
        arsort($internationalProductCounts);

        $top5SpecificInternationalProductsWithCounts = [];
        foreach (array_slice($internationalProductCounts, 0, 5, true) as $product => $count) {
            $top5SpecificInternationalProductsWithCounts[] = [
                'product' => $product,
                'count' => $count,
            ];
        }



        /******************************* Fetch all offers ***********************************/

        $totalDomesticOffers = Inquiry::where('status', 1)->where('offers_status',2)->count();
        $totalInternationalOffers = InternationInquiry::where('status',1)->where('offers_status',2)->count();

        $totalSampleDispatchedOffers = Offer::whereNotNull('sample_dispatched_date')
        ->whereNotNull('sample_send_address')
        ->where('sample_send_address', '!=', '')
        ->count();

        $totalSampleDispatchedInternationalOffers = InternationalOffer::whereNotNull('sample_dispatched_date')
        ->whereNotNull('sample_send_address')
        ->where('sample_send_address', '!=', '')
        ->count();

        $totalSampleDeliveredOffers = Offer::whereNotNull('sample_received_date')
        ->whereNotNull('sample_send_address')
        ->where('sample_send_address', '!=', '')
        ->count();

        $totalSampleDeliveredInternationalOffers = InternationalOffer::whereNotNull('sample_received_date')
        ->whereNotNull('sample_send_address')
        ->where('sample_send_address', '!=', '')
        ->count();

        $totalSampleDispatchedPendingOffers = Offer::whereNull('sample_dispatched_date')->where('status',1)->count();
        $totalSampleDispatchedPendingInternationalOffers = InternationalOffer::whereNull('sample_dispatched_date')->where('status',1)->count();

        $averageSampleAmountReceivedOffers = Offer::whereNotNull("received_sample_amount")->avg("received_sample_amount");
        $averageSampleAmountReceivedInternationalOffers = InternationalOffer::whereNotNull("received_sample_amount")->avg("received_sample_amount");


        //average difference between offer date and third sample dispatch date


        $differenctOfOfferAndSampleDispatchDate = Offer::whereNotNull('offer_date')->get();
        $totalOSDDDays = 0;
        $OSDDcount = 0;
        
            foreach ($differenctOfOfferAndSampleDispatchDate as $offer) {
                $offerDate = Carbon::parse($offer->offer_date);
                $offerSampleDispatchDate = Carbon::parse($offer->sample_dispatched_date);
                $OFCDdiff = $offerSampleDispatchDate->diffInDays($offerDate);
                
                $totalOSDDDays += $OFCDdiff;
                $OSDDcount++;
            }
        
        $averageOfferFCD = $OSDDcount > 0 ? $totalOSDDDays / $OSDDcount : 0;


            /************************** International ******************************/


        $differenctOfInternationalOfferAndSampleDispatchDate = InternationalOffer::whereNotNull('sample_dispatched_date')->get();
        $totalIOSDDDays = 0;
        $IOSDDcount = 0;
        
            foreach ($differenctOfInternationalOfferAndSampleDispatchDate as $offer) {
                $offerDate = Carbon::parse($offer->offer_date);
                $offerSampleDispatchDate = Carbon::parse($offer->sample_dispatched_date);
                $IOFCDdiff = $offerDate->diffInDays($offerSampleDispatchDate);
                
                $totalIOSDDDays += $IOFCDdiff;
                $IOSDDcount++;
            }
        
        $averageInternationalOfferFCD = $IOSDDcount > 0 ? $totalIOSDDDays / $IOSDDcount : 0;




        //average difference between delivery date and third sample dispatch date


        $differenctOfDeliveryAndSampleDispatchDate = Offer::whereNotNull('sample_dispatched_date')->get();

        $totalDSDDDays = 0;
        $DSDDcount = 0;
        
        foreach ($differenctOfDeliveryAndSampleDispatchDate as $offer) {
            $sampleDeliveryDate = Carbon::parse($offer->sample_received_date);
            $offerSampleDispatchDate = Carbon::parse($offer->sample_dispatched_date);
            $DSDDdiff = $offerSampleDispatchDate->diffInDays($sampleDeliveryDate);
            
            $totalDSDDDays += $DSDDdiff;
            $DSDDcount++;
        }
        
        $averagesampleFCD = $DSDDcount > 0 ? $totalDSDDDays / $DSDDcount : 0;

        /************************** International ******************************/

        $differenctOfInternationalDeliveryAndSampleDispatchDate = InternationalOffer::whereNotNull('sample_dispatched_date')->get();
        $totalIODSDDDays = 0;
        $IODSDDcount = 0;
        
        foreach ($differenctOfInternationalDeliveryAndSampleDispatchDate as $offer) {
            $sampleDeliveryDate = Carbon::parse($offer->sample_received_date);
            $offerSampleDispatchDate = Carbon::parse($offer->sample_dispatched_date);
            $IODSDDdiff = $offerSampleDispatchDate->diffInDays($sampleDeliveryDate);
            
            $totalIODSDDDays += $IODSDDdiff;
            $IODSDDcount++;
        }
        
        $averagesampleIOFCD = $IODSDDcount > 0 ? $totalIODSDDDays / $IODSDDcount : 0;



        $offersReceivedSampleAmount = Offer::whereNotNull('received_sample_amount')->sum('received_sample_amount');
        $offersSentSampleAmount = Offer::whereNotNull('sent_sample_amount')->sum('sent_sample_amount');
        
        $offersNetProfitLoss = $offersReceivedSampleAmount - $offersSentSampleAmount;


        $internationalOffersReceivedSampleAmount = InternationalOffer::whereNotNull('received_sample_amount')->sum('received_sample_amount');
        $internationalOffersSentSampleAmount = InternationalOffer::whereNotNull('sent_sample_amount')->sum('sent_sample_amount');
        
        $internationalOffersNetProfitLoss = $internationalOffersReceivedSampleAmount - $internationalOffersSentSampleAmount;


        $offersWithLessThan7Days = Offer::whereNotNull('sample_dispatched_date')
        ->whereNotNull('sample_received_date')
        ->whereRaw('DATEDIFF(sample_received_date, sample_dispatched_date) < 7')
        ->count();

        $topOffers = Offer::whereNotNull('received_sample_amount')
        ->orderByDesc('received_sample_amount')
        ->take(5)
        ->get(['id', 'received_sample_amount', 'offer_number']);


        $internationalOffersWithLessThan7Days = InternationalOffer::whereNotNull('sample_dispatched_date')
        ->whereNotNull('sample_received_date')
        ->whereRaw('DATEDIFF(sample_received_date, sample_dispatched_date) < 7')
        ->count();

        $topInternationalOffers = InternationalOffer::whereNotNull('received_sample_amount')
        ->orderByDesc('received_sample_amount')
        ->take(5)
        ->get(['id', 'received_sample_amount', 'offer_number']);


        //Count how many times each product appears in offers

        $inquiriesWithSpecificProduct = Inquiry::whereNotNull('specific_product')
        ->where('status', 1)
        ->pluck('specific_product');
    
        $offerProductCounts = [];
        foreach ($inquiriesWithSpecificProduct as $productString) {
            $products = array_map('trim', explode(',', $productString));
            foreach ($products as $product) {
                if ($product !== '') {
                    $offerProductCounts[$product] = ($offerProductCounts[$product] ?? 0) + 1;
                }
            }
        }
        
        arsort($offerProductCounts);

        $top5OffersSpecificProductsWithCounts = [];
        foreach (array_slice($offerProductCounts, 0, 5, true) as $product => $count) {
            $top5OffersSpecificProductsWithCounts[] = [
                'product' => $product,
                'count' => $count,
            ];
        }


        $deliveredSampleOffersCount = Offer::where('status', 2)->whereNotNull('sample_received_date')->count();


        $deliveredSampleInternationalOffersCount = InternationalOffer::where('status', 2)->whereNotNull('sample_received_date')->count();



        //Count how many times each product appears in international offers

        $internationalInquiriesWithSpecificProduct = InternationInquiry::whereNotNull('specific_product')
        ->where('status', 1)
        ->pluck('specific_product');
    
        $internationalOfferProductCounts = [];
        
        foreach ($internationalInquiriesWithSpecificProduct as $productString) {
            $products = array_map('trim', explode(',', $productString));
            foreach ($products as $product) {
                if ($product !== '') {
                    $internationalOfferProductCounts[$product] = ($internationalOfferProductCounts[$product] ?? 0) + 1;
                }
            }
        }
        
        arsort($internationalOfferProductCounts);
        
        $top5InternationalOffersSpecificProductsWithCounts = [];
        foreach (array_slice($internationalOfferProductCounts, 0, 5, true) as $product => $count) {
            $top5InternationalOffersSpecificProductsWithCounts[] = [
                'product' => $product,
                'count' => $count,
            ];
        }
        

        $deliveredSampleOffersCount = Offer::whereHas('inquiry', function ($query) {
            $query->where('status', 1);
        })
        ->whereNotNull('sample_received_date')
        ->count();


        $deliveredSampleInternationalOffersCount = InternationalOffer::whereHas('international_inquiry', function ($query) {
            $query->where('status', 1);
        })
        ->whereNotNull('sample_received_date')
        ->count();
        

        /******************************* Fetch all ads ***********************************/

        $totalDomesticAds = Ad::all()->count();
        $totalInternationalAds = InternationalAd::all()->count();
        $totalAds = $totalDomesticAds + $totalInternationalAds;


        $totalAdViews = Ad::all()->whereNotNULL('views')->sum('views');
        $totalInternationalAdViews = InternationalAd::all()->whereNotNULL('views')->sum('views');
        $totalAdViews = $totalAdViews + $totalInternationalAdViews;

        $totalAdReach = Ad::all()->whereNotNULL('reach')->sum('reach');
        $totalInternationalAdReach = InternationalAd::all()->whereNotNULL('reach')->sum('reach');
        $totalAdReach = $totalAdReach + $totalInternationalAdReach;

        $totalMessagesFromUAE = InternationalAd::whereRaw("JSON_CONTAINS(audience, '\"uae\"')")->sum('messages_received');
        $totalMessagesFromIndia = Ad::whereRaw("JSON_CONTAINS(audience, '\"india\"')")->sum('messages_received');
        $totalMessages = $totalMessagesFromUAE + $totalMessagesFromIndia;

        $totalAmountFromUAE = InternationalAd::whereRaw("JSON_CONTAINS(audience, '\"uae\"')")->sum('total_amount_spend');
        $totalAmountFromIndia = Ad::whereRaw("JSON_CONTAINS(audience, '\"india\"')")->sum('total_amount_spend');
        $totalAmountSpend = $totalAmountFromUAE + $totalAmountFromIndia;

        $totalAdAmountSpend = Ad::all()->whereNotNULL('total_amount_spend')->sum('total_amount_spend');
        $totalInternationalAdAmountSpend = InternationalAd::all()->whereNotNULL('total_amount_spend')->sum('total_amount_spend');

        $totalMessagesReceived = Ad::all()->whereNotNULL('messages_received')->sum('messages_received');
        $totalInternationalMessagesReceived = InternationalAd::all()->whereNotNULL('messages_received')->sum('messages_received');

        $costPerMessage = ($totalAdAmountSpend + $totalInternationalAdAmountSpend) / ($totalMessagesReceived +  $totalInternationalMessagesReceived);    


        /******************************* Fetch all orders ***********************************/
        $totalDomesticOrders = Order::all()->count();
        $totalInternationalOrders = InternationalOrder::all()->count();
        $totalOrders = $totalDomesticOrders + $totalInternationalOrders;


        $totalOrderAmount = Order::all()->whereNotNULL('total_amount')->sum('total_amount');

        $totalInternationalOrderAmount = InternationalOrder::all()->whereNotNULL('total_amount')->sum('total_amount');


        $ordersAmountReceived = Order::whereNotNull('amount_received')->sum('amount_received');
        $ordersAmountPaid = Order::whereNotNull('amount_paid')->sum('amount_paid');
        $ordersFinalShippingValue = Order::whereNotNull('buyer_final_shipping_value')->sum('buyer_final_shipping_value');
        $ordersNetProfitLoss = $ordersAmountReceived - $ordersAmountPaid - $ordersFinalShippingValue;


        $internationalOrdersAmountReceived = InternationalOrder::whereNotNull('amount_received')->sum('amount_received');
        $internationalOrdersAmountPaid = InternationalOrder::whereNotNull('amount_paid')->sum('amount_paid');
        $internationalOrdersFinalShippingValue = InternationalOrder::whereNotNull('buyer_final_shipping_value')->sum('buyer_final_shipping_value');
        $internationalOrdersNetProfitLoss = $internationalOrdersAmountReceived - $internationalOrdersAmountPaid - $internationalOrdersFinalShippingValue;


        $averageOrderValue = Order::whereNotNull('amount_received')->where('amount_received', '>', 0)->avg('amount_received');
        $averageInternationalOrderValue = InternationalOrder::whereNotNull('amount_received')->where('amount_received', '>', 0)->avg('amount_received');


        $averageOrderDays = OrderSeller::whereNotNull('order_delivery_date')
        ->whereNotNull('order_dispatch_date')
        ->get()
        ->map(function ($order) {
            return Carbon::parse($order->order_dispatch_date)
                   ->diffInDays(Carbon::parse($order->order_delivery_date));
        })    
        ->avg();

        $averageInternationalOrderDays = InternationalOrderSeller::whereNotNull('order_delivery_date')
        ->whereNotNull('order_dispatch_date')
        ->get()
        ->map(function ($order) {
            return Carbon::parse($order->order_dispatch_date)
                   ->diffInDays(Carbon::parse($order->order_delivery_date));
        })    
        ->avg();

        $pendingOrdersCount = OrderSeller::whereNull('order_dispatch_date')
        ->whereNull('order_delivery_date')
        ->count();

        $pendingInternationalOrdersCount = InternationalOrderSeller::whereNull('order_dispatch_date')
        ->whereNull('order_delivery_date')
        ->count();

        $fastOrdersDelivered = OrderSeller::whereNotNull('order_dispatch_date')
            ->whereNotNull('order_delivery_date')
            ->whereRaw('DATEDIFF(order_delivery_date, order_dispatch_date) < 7')
            ->count();

        $fastInternationalOrdersDelivered = InternationalOrderSeller::whereNotNull('order_dispatch_date')
        ->whereNotNull('order_delivery_date')
        ->whereRaw('DATEDIFF(order_delivery_date, order_dispatch_date) < 7')
        ->count();

        $averageFinalShippingValue = Order::whereNotNull('buyer_final_shipping_value')->avg('buyer_final_shipping_value');

        $averageInternationalFinalShippingValue = InternationalOrder::whereNotNull('buyer_final_shipping_value')->avg('buyer_final_shipping_value');


        $totalWeighofAllOrders = OrderSeller::whereNotNull('weight_per_unit')->sum('weight_per_unit');
        $totalWeighofAllInternationalOrders = InternationalOrderSeller::whereNotNull('weight_per_unit')->sum('weight_per_unit');


        $topOrders = Order::whereNotNull('amount_received')
        ->orderByDesc('amount_received')
        ->take(5)
        ->get();

        $topSellers = OrderSeller::whereNotNull('seller_name')
        ->select('seller_name', DB::raw('COUNT(*) as total_orders'))
        ->groupBy('seller_name')
        ->orderByDesc('total_orders')
        ->limit(5)
        ->get();

        $topProducts = DB::table('order_sellers')
        ->select(
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(products, '$[0].product_name')) as product_name"),
            DB::raw('COUNT(*) as product_count')
        )
        ->whereRaw("JSON_EXTRACT(products, '$[0].product_name') IS NOT NULL")
        ->groupBy(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(products, '$[0].product_name'))"))
        ->orderByDesc('product_count')
        ->limit(10)
        ->get();


        
        $topPayments = Order::whereNotNull('name')
        ->whereNotNull('amount_received')
        ->select('name', 'amount_received')
        ->orderByDesc('amount_received')
        ->limit(10)
        ->get();

        // international order

        $topInternationalOrders = InternationalOrder::whereNotNull('amount_received')
        ->orderByDesc('amount_received')
        ->take(5)
        ->get();

        $topInternationalSellers = InternationalOrderSeller::whereNotNull('seller_name')
        ->select('seller_name', DB::raw('COUNT(*) as total_orders'))
        ->groupBy('seller_name')
        ->orderByDesc('total_orders')
        ->limit(5)
        ->get();

        $topInternationalProducts = InternationalOrderSeller::whereNotNull('product_name')
        ->select('product_name', DB::raw('COUNT(*) as product_count'))
        ->groupBy('product_name')  
        ->orderByDesc('product_count') 
        ->limit(10)
        ->get();
        
        $topInternationalPayments = InternationalOrder::whereNotNull('name')
        ->whereNotNull('amount_received')
        ->select('name', 'amount_received')
        ->orderByDesc('amount_received')
        ->limit(10)
        ->get();



    



        // Return data
        return response()->json([
            'totalInquiriesCount' => $totalInquiriesCount,
            'totalInternationalCount' =>$totalInternationalCount,
            'totalDomestic' => $totalDomestic,
            'totalInternational' => $totalInternational,
            'thisMonthtotalInquiries' => $thisMonthtotalInquiries,
            'thisMonthtotalInternationalInquiries' => $thisMonthtotalInternationalInquiries,
            'totalDomesticCancellations' => $totalDomesticCancellations,
            'totalInternationalCancellations' => $totalInternationalCancellations,
            'inquiryThirdContentNullCount' => $inquiryThirdContentNullCount,
            'internationalInquiryThirdContentNullCount' => $internationalInquiryThirdContentNullCount,
            'internationalInquiryThirdContentNotNullCount' => $internationalInquiryThirdContentNotNullCount,
            'inquiryThirdContentNotNullCount' => $inquiryThirdContentNotNullCount,
            'averageInqFCD' => $averageInqFCD,
            'averageInternationalInqFCD' => $averageInternationalInqFCD,
            'averageInqTCD' => $averageInqTCD,
            'averageInternationalInqTCD' => $averageInternationalInqTCD,
            'top5CategoriesWithCounts' => $top5CategoriesWithCounts,
            'top5InternationalCategoriesWithCounts' => $top5InternationalCategoriesWithCounts,
            'top5SpecificProductsWithCounts' => $top5SpecificProductsWithCounts,
            'top5SpecificInternationalProductsWithCounts' => $top5SpecificInternationalProductsWithCounts,

            // offers

            'totalDomesticOffers' => $totalDomesticOffers,
            'totalInternationalOffers' => $totalInternationalOffers,
            'totalDomesticOrders' => $totalDomesticOrders,
            'totalSampleDispatchedOffers' => $totalSampleDispatchedOffers,
            'totalSampleDispatchedInternationalOffers' => $totalSampleDispatchedInternationalOffers,
            'totalSampleDeliveredOffers' => $totalSampleDeliveredOffers,
            'totalSampleDeliveredInternationalOffers' => $totalSampleDeliveredInternationalOffers,
            'totalSampleDispatchedPendingOffers' => $totalSampleDispatchedPendingOffers,
            'totalSampleDispatchedPendingInternationalOffers' => $totalSampleDispatchedPendingInternationalOffers,
            'averageSampleAmountReceivedOffers' => $averageSampleAmountReceivedOffers,
            'averageSampleAmountReceivedInternationalOffers' => $averageSampleAmountReceivedInternationalOffers,
            'averageOfferFCD' => $averageOfferFCD,
            'averageInternationalOfferFCD' => $averageInternationalOfferFCD,
            'averagesampleFCD' => $averagesampleFCD,
            'averagesampleIOFCD' => $averagesampleIOFCD,
            'deliveredSampleOffersCount' => $deliveredSampleOffersCount,
            'deliveredSampleInternationalOffersCount' => $deliveredSampleInternationalOffersCount,
            'offersNetProfitLoss' => $offersNetProfitLoss,
            'internationalOffersNetProfitLoss' => $internationalOffersNetProfitLoss,
            'offersWithLessThan7Days' => $offersWithLessThan7Days,
            'internationalOffersWithLessThan7Days' => $internationalOffersWithLessThan7Days,
            'topOffers' => $topOffers,
            'topInternationalOffers' => $topInternationalOffers,
            'top5OffersSpecificProductsWithCounts' => $top5OffersSpecificProductsWithCounts,
            'top5InternationalOffersSpecificProductsWithCounts' => $top5InternationalOffersSpecificProductsWithCounts,

            //ads

            'totalAds' => $totalAds,
            'totalAdViews' => $totalAdViews,
            'totalAdReach' => $totalAdReach,
            'totalMessagesFromUAE' => $totalMessagesFromUAE,
            'totalMessagesFromIndia' => $totalMessagesFromIndia,
            'totalMessages' => $totalMessages,
            'totalAmountSpend' => $totalAmountSpend,
            'totalAmountFromUAE' => $totalAmountFromUAE,
            'totalAmountFromIndia' => $totalAmountFromIndia,
            'totalAmountSpend' => $totalAmountSpend,
            'costPerMessage' => $costPerMessage,

            //orders

            'totalOrders' => $totalOrders,
            'totalOrderAmount' => $totalOrderAmount,
            'totalInternationalOrderAmount' => $totalInternationalOrderAmount,
            'ordersAmountReceived' => $ordersAmountReceived,
            'ordersAmountPaid' => $ordersAmountPaid,
            'ordersFinalShippingValue' => $ordersFinalShippingValue,
            'ordersNetProfitLoss' => $ordersNetProfitLoss,
            'internationalOrdersAmountReceived' => $internationalOrdersAmountReceived,
            'internationalOrdersAmountPaid' => $internationalOrdersAmountPaid,
            'internationalOrdersFinalShippingValue' => $internationalOrdersFinalShippingValue,
            'internationalOrdersNetProfitLoss' => $internationalOrdersNetProfitLoss,
            'averageOrderValue' => $averageOrderValue,
            'averageInternationalOrderValue' => $averageInternationalOrderValue,
            'averageOrderDays' => $averageOrderDays,
            'averageInternationalOrderDays' => $averageInternationalOrderDays,
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingInternationalOrdersCount' => $pendingInternationalOrdersCount,
            'fastOrdersDelivered' => $fastOrdersDelivered,
            'fastInternationalOrdersDelivered' => $fastInternationalOrdersDelivered,
            'averageFinalShippingValue' => round($averageFinalShippingValue,2),
            'averageInternationalFinalShippingValue' => round($averageInternationalFinalShippingValue,2),
            'totalWeighofAllOrders'  => $totalWeighofAllOrders,
            'totalWeighofAllInternationalOrders' => $totalWeighofAllInternationalOrders,
            'topOrders' => $topOrders,
            'topInternationalOrders' => $topInternationalOrders,
            'topSellers' => $topSellers,
            'topInternationalSellers' => $topInternationalSellers,
            'topProducts' => $topProducts,
            'topInternationalProducts' => $topInternationalProducts,
            'topPayments' => $topPayments,
            'topInternationalPayments' => $topInternationalPayments

        ]);
    }
    
    

}
