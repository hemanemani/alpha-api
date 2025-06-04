<?php

namespace App\Http\Controllers;

use App\Models\InternationInquiry;
use Illuminate\Http\Request;
use App\Imports\InternationalInquiryImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BlockedInternationalInquiry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\Models\InternationalOffer;
use App\Models\BlockedInternationalOffer;
use App\Models\UploadInternationalInquiry;
use Illuminate\Support\Facades\Auth;
use App\Rules\UniqueMobileAcrossTables;
use App\Models\BlockedInternationalOrder;
use App\Models\InternationalOrderSeller;
use App\Models\InternationalOrder;
use Illuminate\Support\Facades\Log;


class InternationalInquiryController extends Controller
{
    /**
     * Display a listing of the inquiries.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/international_inquiries",
     *     summary="Get International Inquiry details",
     *     description="Retrieve the information of the international inquiry.",
     *     operationId="international_inquiry",
     *     tags={"International Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function index()
    {
        $inquiries = InternationInquiry::with('user')->where('status','2')->orderBy('id', 'desc')->get();
        return response()->json($inquiries);
    }

    /**
     * @OA\Get(
     *     path="/api/inquiry-approved-international-offer",
     *     summary="Get International Inquiry details",
     *     description="Retrieve the information of the international inquiry.",
     *     operationId="international_approved_inquiry",
     *     tags={"International Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function total_index()
    {
        $inquiries = InternationInquiry::all();

        $sampleStatusCounts = [
            'notDispatched' => InternationalOffer::whereNull('sample_dispatched_date')->count(),
            'dispatchedOnly' => InternationalOffer::whereNotNull('sample_dispatched_date')->whereNull('sample_received_date')->count(),
            'bothFilled' => InternationalOffer::whereNotNull('sample_dispatched_date')->whereNotNull('sample_received_date')->count()
        ];

        $domesticAdPlatform = \App\Models\Ad::select('platform', \DB::raw('SUM(messages_received) as total'))
        ->whereNotNull('messages_received')
        ->groupBy('platform')
        ->get();



        $internationalAdPlatform = \App\Models\InternationalAd::select('platform', \DB::raw('SUM(messages_received) as total'))
            ->whereNotNull('messages_received')
            ->groupBy('platform')
            ->get();


        $combinedAds = collect();

        foreach (['instagram', 'meta', 'facebook'] as $platform) {
            $domesticTotal = $domesticAdPlatform->firstWhere('platform', $platform)->total ?? 0;
            $internationalTotal = $internationalAdPlatform->firstWhere('platform', $platform)->total ?? 0;
        
            $combinedAds->push([
                'platform' => ucfirst($platform),
                'total' => $domesticTotal + $internationalTotal,
            ]);
        }



        $deliveredOrderedCount = InternationalOrderSeller::whereNotNull('order_dispatch_date')
            ->whereNotNull('order_delivery_date')
            ->count();
        
        $dispatchedOrderedCount = InternationalOrderSeller::whereNotNull('order_dispatch_date')
            ->whereNull('order_delivery_date')
            ->count();
        
        $pendingOrderedCount = InternationalOrderSeller::whereNull('order_dispatch_date')
            ->count();

        $totalSellerOrderCount = $deliveredOrderedCount + $dispatchedOrderedCount + $pendingOrderedCount;


        $shipRocketCount = InternationalOrderSeller::where('logistics_through', 'ship_rocket')->count();
        $sellerFulfilledCount = InternationalOrderSeller::where('logistics_through', 'seller_fulfilled')->count();
        $totalLogisticsCount = $shipRocketCount + $sellerFulfilledCount;

        return response()->json([
            'inquiries' => $inquiries,
            'sampleStatusCounts' => $sampleStatusCounts,
            'combinedAds' => $combinedAds,
            'orderDispatchData' => [
                'delivered' => $deliveredOrderedCount,
                'dispatched' => $dispatchedOrderedCount,
                'pending' => $pendingOrderedCount,
                'total' => $totalSellerOrderCount,
            ],
            'logisticsData' => [
                'ship_rocket' => $shipRocketCount,
                'seller_fulfilled' => $sellerFulfilledCount,
                'total' => $totalLogisticsCount,
            ],
    
        ]
            
        );

    }

    public function approved_offers()
    {
        $approved_offers = InternationInquiry::with(['user','international_offers'])
            ->where('status', 1)
            ->where('offers_status',2)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($international_inquiry) {
                $international_offer = \App\Models\InternationalOffer::where('international_inquiry_id', $international_inquiry->id)->first();
                $international_inquiry->offer_number = $international_offer?->offer_number ?? null;
                return $international_inquiry;
            });

        return response()->json($approved_offers);
    }
    /**
     * @OA\Get(
     *     path="/api/inquiry-cancellation-international-offers",
     *     summary="Get International Inquiry details",
     *     description="Retrieve the information of the international inquiry.",
     *     operationId="international_cancellation_inquiry",
     *     tags={"International Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function cancellation_offers()
    {
        $blockedNumbers = DB::table('blocked_international_inquiries')->pluck('mobile_number')->toArray();
        $cancelled_offers = InternationInquiry::with('user')
        ->where('status', 0)
        ->orderBy('id', 'desc')
        ->whereNotIn('mobile_number', $blockedNumbers)
        ->get();
        return response()->json($cancelled_offers);
    }

   

    /**
     * Store a newly created InternationInquiry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/international_inquiries",
     *     summary="Create a new international inquiry",
     *     tags={"International Inquiries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="International Inquiry created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="International Inquiry created successfully"),
     *             @OA\Property(property="inquiry", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

     public function blockInternationalInquiry(Request $request)
     {
 
         BlockedInternationalInquiry::create([
             'mobile_number' => $request->mobile_number,
         ]);
     
         return response()->json([
             'message' => 'International Inquiry blocked successfully.',
             'success' => true,
         ], 200);
     
     }

     public function blockInternationalOffer(Request $request)
     {
 
         BlockedInternationalOffer::create([
             'mobile_number' => $request->mobile_number,
         ]);
     
         return response()->json([
             'message' => 'International Inquiry blocked successfully.',
             'success' => true,
         ], 200);
     
     }

     public function blockInternationalOrder(Request $request)
    {

        BlockedInternationalOrder::create([
            'mobile_number' => $request->mobile_number,
        ]);
    
        return response()->json([
            'message' => 'Order blocked successfully.',
            'success' => true,
        ], 200);
    
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'inquiry_number' => 'required',
            'mobile_number' => 'required|string',
            'inquiry_date' => 'required|date',
            'product_categories' => 'nullable|string',
            'specific_product' => 'nullable|string',
            'name' => 'required|string',
            'location' => 'nullable|string',
            'inquiry_through' => 'nullable|string',
            'inquiry_reference' => 'nullable|string',
            'first_contact_date' => 'required|date',
            'first_response' => 'required|string',
            'second_contact_date' => 'nullable|date',
            'second_response' => 'nullable|string',
            'third_contact_date' => 'nullable|date',
            'third_response' => 'nullable|string',
            'notes' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'select_user' => 'nullable|string'

        ]);

        $request->validate([
            'mobile_number' => ['required', new UniqueMobileAcrossTables],
        ]);

        $inquiry_date = $request->inquiry_date 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->inquiry_date)->format('Y-m-d') 
            : null;

        $first_contact_date = $request->first_contact_date 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->first_contact_date)->format('Y-m-d') 
            : null;

        $second_contact_date = $request->second_contact_date 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->second_contact_date)->format('Y-m-d') 
            : null;

        $third_contact_date = $request->third_contact_date 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->third_contact_date)->format('Y-m-d') 
            : null;

        $international_inquiry = InternationInquiry::create([
            'inquiry_number' => $validated['inquiry_number'],
            'mobile_number' => $validated['mobile_number'],
            'inquiry_date' => $inquiry_date,
            'product_categories' => $validated['product_categories'],
            'specific_product' => $validated['specific_product'],
            'name' => $validated['name'],
            'location' => $validated['location'],
            'inquiry_through' => $validated['inquiry_through'],
            'inquiry_reference' => $validated['inquiry_reference'],
            'first_contact_date' => $first_contact_date,
            'first_response' => $validated['first_response'],
            'second_contact_date' => $second_contact_date,
            'second_response' => $validated['second_response'],
            'third_contact_date' => $third_contact_date,
            'third_response' => $validated['third_response'],
            'select_user' => $validated['select_user'],
            'notes' => $validated['notes'],
            'user_id' => $validated['user_id'],

        ]);
        return response()->json([
            'success' => true,
            'message' => 'International Inquiry Created Successfully',
            'international_inquiry' => $international_inquiry
        ], 201);
                        
    
    }

    /**
     * Display the specified InternationInquiry.
     *
     * @param  \App\Models\InternationInquiry  $international_inquiry
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/international_inquiries/{id}",
     *     summary="Get details of a specific International Inquiry",
     *     tags={"International Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the international inquiry to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="International Inquiry retrieved successfully."),
     *             @OA\Property(property="inquiry", type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="International Inquiry not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="International Inquiry not found.")
     *         )
     *     )
     * )
     */
    public function show(InternationInquiry $international_inquiry)
    {
        if (!$international_inquiry) {
            return response()->json([
                'success' => false,
                'message' => 'International Inquiry not found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'international_inquiry' => $international_inquiry,
        ], 200);
    }

    

    /**
     * Update the specified international inquiry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InternationInquiry  $international_inquiry
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *     path="/api/international_inquiries/{id}",
     *     summary="Update an existing international inquiry",
     *     tags={"International Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the international inquiry to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="International Inquiry updated successfully"),
     *             @OA\Property(property="inquiry", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="International Inquiry not found"
     *     )
     * )
     */

     public function getInternationalInquiryWithOffers($id)
     {
         $international_inquiry = InternationInquiry::where('id', $id)->first();
 
         if (!$international_inquiry) {
             return response()->json(['message' => 'International Inquiry not found'], 404);
         }
 
         $international_offers = InternationalOffer::where('international_inquiry_id', $id)->get();
 
         return response()->json([
             'international_inquiry' => $international_inquiry,
             'international_offers' => $international_offers,
         ]);
 
     }

     private function parseDate($date) {
        if (empty($date)) {
            return null;
        }
    
        $formats = ['d-m-Y', 'Y-m-d', 'd/m/Y', 'm-d-Y', 'm/d/Y'];
    
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date)->format('Y-m-d');
            } catch (Exception $e) {
                continue; // Try the next format
            }
        }
        
        return null; // Return null if no format matched
    }
    
    public function update(Request $request, InternationInquiry $international_inquiry)
    {

        $validated = $request->validate([
            'inquiry_number' => 'sometimes|integer',
            'mobile_number' => ['required', 'string', new UniqueMobileAcrossTables($international_inquiry->id)],
            'inquiry_date' => 'sometimes|date',
            'product_categories' => 'nullable|string',
            'specific_product' => 'nullable|string',
            'name' => 'sometimes|string',
            'location' => 'nullable|string',
            'inquiry_through' => 'nullable|string',
            'inquiry_reference' => 'nullable|string',
            'first_contact_date' => 'sometimes|date',
            'first_response' => 'sometimes|string',
            'second_contact_date' => 'nullable|date',
            'second_response' => 'nullable|string',
            'third_contact_date' => 'nullable|date',
            'third_response' => 'nullable|string',
            'notes' => 'nullable|string',
            'select_user' => 'nullable|string',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'nullable|integer',
            'offers_status' => 'nullable|integer',
            
            

            //offers
            'international_inquiry_id' => 'sometimes|exists:international_inquiries,id',
            'offer_number' => 'sometimes|string',
            'offer_date' => 'sometimes|date',
            'communication_date' => 'sometimes|date',
            'received_sample_amount' => 'sometimes|integer',
            'sent_sample_amount' => 'sometimes|integer',
            'sample_dispatched_date' => 'sometimes|date',
            'sample_sent_through' => 'sometimes|string',
            'sample_received_date' => 'sometimes|date',
            'offer_notes' => 'sometimes|string',
            'sample_send_address' => 'sometimes|string'


        ]);

        $inquiry_date = $this->parseDate($request->inquiry_date);
        $first_contact_date = $this->parseDate($request->first_contact_date);
        $second_contact_date = $this->parseDate($request->second_contact_date);
        $third_contact_date = $this->parseDate($request->third_contact_date);
    
        // $international_inquiry->update([
        //     'inquiry_number' => $validated['inquiry_number'],
        //     'mobile_number' => $validated['mobile_number'],
        //     'inquiry_date' => $inquiry_date,
        //     'product_categories' => $validated['product_categories'],
        //     'specific_product' => $validated['specific_product'],
        //     'name' => $validated['name'],
        //     'location' => $validated['location'],
        //     'inquiry_through' => $validated['inquiry_through'],
        //     'inquiry_reference' => $validated['inquiry_reference'],
        //     'first_contact_date' => $first_contact_date,
        //     'first_response' => $validated['first_response'],
        //     'second_contact_date' => $second_contact_date,
        //     'second_response' => $validated['second_response'],
        //     'third_contact_date' => $third_contact_date,
        //     'third_response' => $validated['third_response'],
        //     'notes' => $validated['notes'],
        //     'user_id' => $validated['user_id'],
        //     'status' => $validated['status'],
        //     'offers_status' => $request->has('offers_status') ? $validated['offers_status'] : 2,

        // ]);

        $international_inquiry->inquiry_number = $validated['inquiry_number'] ?? $inquiry->inquiry_number;
        $international_inquiry->mobile_number = $validated['mobile_number'];
        $international_inquiry->inquiry_date = $inquiry_date;
        $international_inquiry->product_categories = $validated['product_categories'];
        $international_inquiry->specific_product = $validated['specific_product'];
        $international_inquiry->name = $validated['name'];
        $international_inquiry->location = $validated['location'];
        $international_inquiry->inquiry_through = $validated['inquiry_through'];
        $international_inquiry->inquiry_reference = $validated['inquiry_reference'];
        $international_inquiry->first_contact_date = $first_contact_date;
        $international_inquiry->first_response = $validated['first_response'];
        $international_inquiry->second_contact_date = $second_contact_date;
        $international_inquiry->second_response = $validated['second_response'];
        $international_inquiry->third_contact_date = $third_contact_date;
        $international_inquiry->third_response = $validated['third_response'];
        $international_inquiry->notes = $validated['notes'];
        $international_inquiry->status = $validated['status'];
        $international_inquiry->offers_status = $request->has('offers_status') ? $validated['offers_status'] : 2;
        $inquiry->select_user = $validated['select_user'];

        $international_inquiry->save();

        $offerData = $request->input('offer_data', []);

        $offer_date = isset($offerData['offer_date']) 
        ? \Carbon\Carbon::parse($offerData['offer_date']) // Direct parsing
        : null;

        $communication_date = isset($offerData['communication_date']) 
        ? \Carbon\Carbon::parse($offerData['communication_date']) // Direct parsing
        : null;

        $sample_dispatched_date = isset($offerData['sample_dispatched_date']) 
            ? \Carbon\Carbon::parse($offerData['sample_dispatched_date']) 
            : null;

        $sample_received_date = isset($offerData['sample_received_date']) 
            ? \Carbon\Carbon::parse($offerData['sample_received_date']) 
            : null;    


        $international_offer = null;
        if ($international_inquiry->status == 1) {

            $existingInternationalOffer = \App\Models\InternationalOffer::where('international_inquiry_id', $international_inquiry->id)->first();

            if (!$existingInternationalOffer) {
                $lastInternationalOfferNumber = \App\Models\InternationalOffer::max('offer_number') ?? 0;
                $newInternationalOfferNumber = $lastInternationalOfferNumber + 1;
            }

            $international_offer = InternationalOffer::updateOrCreate(
                ['international_inquiry_id' => $international_inquiry->id],
                [
                    'offer_number' => $existingInternationalOffer->offer_number ?? $newInternationalOfferNumber,
                    'offer_date' => $offer_date,
                    'communication_date' => $communication_date,
                    'received_sample_amount' => $offerData['received_sample_amount'] ?? null,
                    'sent_sample_amount' => $offerData['sent_sample_amount'] ?? null,
                    'sample_dispatched_date' => $sample_dispatched_date,
                    'sample_sent_through' => $offerData['sample_sent_through'] ?? null,
                    'sample_received_date' => $sample_received_date,
                    'offer_notes' => $offerData['offer_notes'] ?? null,
                    'sample_send_address' => $offerData['sample_send_address'] ?? null,

                ]
            );

            // Save offer if it changed 

            if ($international_offer->isDirty()) {
                $international_offer->save();
            }


            if ($international_inquiry->offers_status == 1) {
                $lastInternationalOrderNumber = \App\Models\InternationalOrder::max('order_number') ?? 56564;
                $newInternationalOrderNumber = $lastInternationalOrderNumber + 1;
        
                \App\Models\InternationalOrder::create([
                    'order_number' => $newInternationalOrderNumber,
                    'international_offer_id' => $international_offer->id,
                ]);
            }
        }else {
            $international_offer = null;
        }

        // ---- Dirty check: update user_id if inquiry OR offer is modified ----

        if (
        $international_inquiry->wasChanged() ||
        ($international_offer instanceof \App\Models\InternationalOffer && $international_offer->wasChanged())
        ) {
            $international_inquiry->user_id = $validated['user_id'];
            $international_inquiry->save();
        }

        return response()->json([
            'message' => 'Inquiry updated successfully.',
            'international_inquiry' =>$international_inquiry,
            'international_offer' => $international_offer
        ], 200);

    }

    public function offerInternationalCancellations()
    {

        $offer_international_cancellations = InternationInquiry::with(['user','international_offers'])
            ->where('status', 1)
            ->where('offers_status', 0)
            ->orderBy('id', 'desc')
            ->whereNotIn('mobile_number', function ($subquery) {
                $subquery->select('mobile_number')->from('blocked_international_offers');
            })
            ->get()
            ->map(function ($international_inquiry) {
                $international_offer = \App\Models\InternationalOffer::where('international_inquiry_id', $international_inquiry->id)->first();
                $international_inquiry->offer_number = $international_offer?->offer_number ?? null;
                return $international_inquiry;
            });

        return response()->json($offer_international_cancellations);

    }

    public function orderInternationalCancellations()
    {
        $combinedResults = collect();

        $order_international_cancellations = InternationInquiry::with('user')
            ->where('status', 1)
            ->where('offers_status', 1)
            ->where('orders_status', 0)
            ->orderBy('id', 'desc')
            ->whereNotIn('mobile_number', function ($subquery) {
                $subquery->select('mobile_number')->from('blocked_international_orders');
            })
            ->get()
            ->map(function ($international_inquiry) {
                $international_offers = \App\Models\InternationalOffer::where('international_inquiry_id', $international_inquiry->id)->get();

                $international_offers->map(function ($international_offer) {
                    $international_order = \App\Models\InternationalOrder::with('international_sellers','user')->where('international_offer_id', $international_offer->id)->first();
                    $international_offer->international_order = $international_order;
                    return $international_offer;
                });
    
    
                $international_inquiry->international_offers = $international_offers;
    
                return $international_inquiry;
            });

        $combinedResults = $combinedResults->merge($order_international_cancellations);

        // Step 2: Get standalone orders where status = 0
        $internationalorders = \App\Models\InternationalOrder::with(['international_sellers','user'])
        ->where('status', 0)
        ->get();

        // Add to combined list
        $combinedResults = $combinedResults->merge($internationalorders);
    


        return response()->json($combinedResults);
    }

    public function updateInternationInquiryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|integer',
            'offers_status' => 'nullable|integer',
            'orders_status' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',

        ]);

        $international_order = InternationalOrder::find($id);

        if ($international_order && is_null($international_order->international_offer_id)) {
            $international_order->status = $request->orders_status ?? $international_order->status;
            $international_order->user_id = $request->user_id ?? $international_order->user_id;
            $international_order->save();



            return response()->json([
                'success' => true,
                'message' => 'Standalone Order status updated successfully.',
                'responseMessage' => 'International Order status updated (without inquiry).'
            ]);
            
        }

        $international_inquiry = InternationInquiry::find($id);


        if ($international_inquiry) {
            $international_inquiry->status = $request->status ?? $international_inquiry->status;
            $international_inquiry->offers_status = $request->offers_status ?? $international_inquiry->offers_status;
            $international_inquiry->orders_status = $request->orders_status ?? $international_inquiry->orders_status;
            $international_inquiry->user_id = $request->user_id ?? $international_inquiry->user_id;

        
            $international_inquiry->save();
        
            if ($request->status === 0) {
                $responseMessage = 'International Inquiry moved to Cancellations.';
            } elseif ($request->status === 1 && $international_inquiry->offers_status === 2) {
                // Move to offers
                $lastOfferNumber = \App\Models\InternationalOffer::max('offer_number') ?? 0;
                $newOfferNumber = $lastOfferNumber + 1;
        
                $international_offer = \App\Models\InternationalOffer::firstOrNew(['international_inquiry_id' => $international_inquiry->id]);
                $international_offer->offer_number = $newOfferNumber;
                $international_offer->international_inquiry_id = $international_inquiry->id;
                $international_offer->save();
        
                $responseMessage = 'International Inquiry moved to Offers and offer number created.';
            } elseif ($request->status === 1 && $request->offers_status === 0) {
                $responseMessage = 'International Inquiry moved to Offer Cancellations.';
            } elseif ($request->status === 1 && $request->offers_status === 1 && $request->orders_status === 0) {
                $responseMessage = 'International Inquiry moved to Orders Cancellations.';
            } elseif ($request->status === 1 && $request->offers_status === 1) {
                $international_offer = \App\Models\InternationalOffer::firstOrNew(['international_inquiry_id' => $international_inquiry->id]);
                if($international_offer){
                    $international_offer->status = 1;
                    $international_offer->save();
                } 
                // Move to orders
                $lastOrderNumber = \App\Models\InternationalOrder::max('order_number');
                if ($lastOrderNumber === null || $lastOrderNumber < 56564) {
                    $lastOrderNumber = 56564;
                }
                $newOrderNumber = $lastOrderNumber + 1;

        
                $international_offer = \App\Models\InternationalOffer::where('international_inquiry_id', $international_inquiry->id)->first();
        
                if ($international_offer) {
                    $international_order = \App\Models\InternationalOrder::firstOrNew(['international_offer_id' => $international_offer->id]);
                    $international_order->order_number = $newOrderNumber;
                    $international_order->international_offer_id = $international_offer->id;
                    $international_order->save();
        
                    $responseMessage = 'International Inquiry moved to Orders and order number updated.';
                } else {
                    $responseMessage = 'Offer not found for the inquiry.';
                }
            }
        } else {
            $international_order = InternationalOrder::find($id);
            if ($international_order) {
                $international_order->status = $request->orders_status ?? $international_order->status;
                $international_order->save();
        
                $responseMessage = 'Order status updated (without inquiry).';
            }
        }
        
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'responseMessage' => $responseMessage
        ]);
    }

    
    /**
     * @OA\Post(
     *     path="/api/international-inquiries/bulk-upload",
     *     summary="Bulk upload international inquiries",
     *     description="Upload a CSV or TXT file to bulk import international inquiries into the system.",
     *     operationId="internationalBulkUpload",
     *     tags={"International Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="CSV or TXT file containing international inquiries to upload.",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="The CSV or TXT file to upload."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiries imported successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="International Inquiries imported successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The file field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during file upload",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="There was an error during import."),
     *             @OA\Property(property="error", type="string", example="Detailed error message.")
     *         )
     *     )
     * )
     */
    

    public function bulkUpload(Request $request)
    {
         try {
             $request->validate([
                 'file' => 'required|mimes:csv,txt|max:2048',
             ]);
         
             $user = Auth::user();
             $file = $request->file('file');
             $fileName = time() . '_' . $file->getClientOriginalName();
             $filePath = 'uploads/' . $fileName;
             $fileSize = $file->getSize(); // Size in bytes
 
         
             // Move the file to the public/uploads directory
             $file->move(public_path('uploads'), $fileName);
         
             // Ensure the file exists after move operation
             if (!file_exists(public_path($filePath))) {
                 return response()->json([
                     'success' => false,
                     'message' => 'File upload failed.',
                     'status'  => 'Upload failed',
                 ], 500);
             }
 
             $import = new InternationalInquiryImport();
             Excel::import($import, public_path($filePath));
 
             if (!empty($import->getErrors())) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Upload failed with errors.',
                     'errors'  => $import->getErrors()
                 ], 422);
             }
             
     
             
             // Store upload record in the database
             UploadInternationalInquiry::create([
                 'uploaded_by' => $user->id,
                 'file_name'   => $file->getClientOriginalName(),
                 'file_path'   => $filePath,
                 'uploaded_at' => now(),
                 'status'      => 'Uploaded',
                 'file_size'   => $fileSize
             ]);
         
             return response()->json([
                 'success'   => true,
                 'message'   => 'Upload successful!',
                 'file_path' => asset($filePath), // Convert to full URL for frontend
                 'file_size' => $fileSize, // Return file size
                 'status'    => 'Uploaded',
                 'file_name' => $fileName
             ], 200);
         
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'There was an error during import.',
                 'error'   => $e->getMessage(),
                 'status'  => 'Upload failed',
             ], 500);
         }
         
    }

    public function bulkUploadData(){
        $uploadinternationalinquirydata = UploadInternationalInquiry::with('user:id,name')->get();
        return response()->json($uploadinternationalinquirydata);
    }

    public function uploadDestroy($id)
    {
        $uploadInternationalInquiry = UploadInternationalInquiry::find($id);
        if (!$uploadInternationalInquiry) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $uploadInternationalInquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Upoaded Data deleted successfully.',
        ], 200);
    }
    
    
    public function downloadTemplate()
    {
        $headers = [
            'mobile_number',
            'inquiry_date',
            'product_categories',
            'specific_product',
            'name',
            'location',
            'inquiry_through',
            'inquiry_reference',
            'first_contact_date',
            'first_response',
            'second_contact_date',
            'second_response',
            'third_contact_date',
            'third_response',
            'notes'
        ];

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $headers;

            public function __construct($headers)
            {
                $this->headers = $headers;
            }

            public function array(): array
            {
                return [$this->headers];
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Concerns\WithEvents::class => function ($event) {
                        $event->sheet->getDelegate()->getWriter()->setDelimiter(',')
                            ->getDelegate()->setUTF8();
                    },
                ];
            }
        }, 'international_inquiry_template.xlsx');

    }


    /**
     * Remove the specified inquiry from storage.
     *
     * @param  \App\Models\InternationInquiry  $international_inquiry
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     *     path="/api/international_inquiries/{id}",
     *     summary="Delete a International inquiry",
     *     tags={"International Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the international inquiry to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="International Inquiry deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="International Inquiry deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="International Inquiry not found"
     *     )
     * )
     */
    public function destroy(InternationInquiry $international_inquiry)
    {
        $international_inquiry->delete();
        return response()->json([
            'success' => true,
            'message' => 'International Inquiry deleted successfully.',
        ],200);
    }

    public function getNextInternationalInquiryNumber()
    {
        $nextNumber = \App\Models\Inquiry::max('inquiry_number') + 1;
        return response()->json(['next_inquiry_number' => $nextNumber]);
    }
}
