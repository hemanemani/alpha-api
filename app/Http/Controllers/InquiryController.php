<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use App\Imports\InquiryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UploadInquiry;
use App\Models\BlockedInquiry;
use App\Models\Offer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\BlockedOffer;
use Illuminate\Support\Facades\Schema;
use App\Rules\UniqueMobileAcrossTables;
use App\Models\BlockedOrder;
use App\Models\OrderSeller;
use App\Models\Order;

class InquiryController extends Controller
{

    /**
     * Display a listing of the inquiries.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/inquiries",
     *     summary="Get Inquiry details",
     *     description="Retrieve the information of the inquiry.",
     *     operationId="inquiry",
     *     tags={"Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function index()
    {
        $inquiries = Inquiry::with('user')->where('status','2')->get();
        return response()->json($inquiries);
    }

    public function total_index()
    {
        $inquiries = Inquiry::all();

        $sampleStatusCounts = [
            'notDispatched' => Offer::whereNull('sample_dispatched_date')->count(),
            'dispatchedOnly' => Offer::whereNotNull('sample_dispatched_date')->whereNull('sample_received_date')->count(),
            'bothFilled' => Offer::whereNotNull('sample_dispatched_date')->whereNotNull('sample_received_date')->count()
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



        $deliveredOrderedCount = OrderSeller::whereNotNull('order_dispatch_date')
            ->whereNotNull('order_delivery_date')
            ->count();
        
        $dispatchedOrderedCount = OrderSeller::whereNotNull('order_dispatch_date')
            ->whereNull('order_delivery_date')
            ->count();
        
        $pendingOrderedCount = OrderSeller::whereNull('order_dispatch_date')
            ->count();

        $totalSellerOrderCount = $deliveredOrderedCount + $dispatchedOrderedCount + $pendingOrderedCount;


        $shipRocketCount = Order::where('logistics_through', 'ship_rocket')->count();
        $sellerFulfilledCount = Order::where('logistics_through', 'seller_fulfilled')->count();
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
    /**
     * @OA\Get(
     *     path="/api/inquiry-approved-offers",
     *     summary="Get Inquiry details",
     *     description="Retrieve the information of the inquiry.",
     *     operationId="approved_inquiry",
     *     tags={"Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function approved_offers()
    {
        $approved_offers = Inquiry::with('user') 
            ->where('status', 1)
            ->where('offers_status',2)
            ->get()
            ->map(function ($inquiry) {
                $offer = \App\Models\Offer::where('inquiry_id', $inquiry->id)->first();
                $inquiry->offer_number = $offer?->offer_number ?? null;
                return $inquiry;
            });

        return response()->json($approved_offers);
    }


    /**
     * @OA\Get(
     *     path="/api/inquiry-cancellation-offers",
     *     summary="Get Inquiry details",
     *     description="Retrieve the information of the inquiry.",
     *     operationId="cancellation_inquiry",
     *     tags={"Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry details retrieved successfully.",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated.")
     * )
     */
    public function cancellation_offers()
    {

        $blockedNumbers = DB::table('blocked_inquiries')->pluck('mobile_number')->toArray();
        $cancelled_offers = Inquiry::with('user')
            ->where('status', 0)
            ->whereNotIn('mobile_number', $blockedNumbers)
            ->get();
    
        return response()->json($cancelled_offers);

    }

    /**
     * Store a newly created inquiry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/inquiries",
     *     summary="Create a new inquiry",
     *     tags={"Inquiries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inquiry created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inquiry created successfully"),
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
    public function blockInquiry(Request $request)
    {

        BlockedInquiry::create([
            'mobile_number' => $request->mobile_number,
        ]);
    
        return response()->json([
            'message' => 'Inquiry blocked successfully.',
            'success' => true,
        ], 200);
    
    }

    public function blockOffer(Request $request)
    {

        BlockedOffer::create([
            'mobile_number' => $request->mobile_number,
        ]);
    
        return response()->json([
            'message' => 'Inquiry blocked successfully.',
            'success' => true,
        ], 200);
    
    }

    public function blockOrder(Request $request)
    {

        BlockedOrder::create([
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
        ]);

        if (BlockedInquiry::where('mobile_number', $request->mobile_number)->exists() || BlockedOffer::where('mobile_number', $request->mobile_number)->exists() || BlockedOrder::where('mobile_number', $request->mobile_number)->exists() ) {
            return response()->json(['message' => 'This inquiry is blocked.'], 403);
        }


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

        $inquiry = Inquiry::create([
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
            'notes' => $validated['notes'],
            'user_id' => $validated['user_id'],
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Inquiry Created Successfully',
            'inquiry' => $inquiry
        ], 201);
                        
    
    }

    /**
     * Display the specified inquiry.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/inquiries/{id}",
     *     summary="Get details of a specific Inquiry",
     *     tags={"Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inquiry to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inquiry retrieved successfully."),
     *             @OA\Property(property="inquiry", type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Inquiry not found.")
     *         )
     *     )
     * )
     */
    public function show(Inquiry $inquiry)
    {
        if (!$inquiry) {
            return response()->json([
                'success' => false,
                'message' => 'Inquiry not found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'inquiry' => $inquiry,
        ], 200);
    }

    public function getInquiryWithOffers($id)
    {
        $inquiry = Inquiry::where('id', $id)->first();

        if (!$inquiry) {
            return response()->json(['message' => 'Inquiry not found'], 404);
        }

        $offers = Offer::where('inquiry_id', $id)->get();

        return response()->json([
            'inquiry' => $inquiry,
            'offers' => $offers,
        ]);

    }
    

   

    /**
     * Update the specified inquiry in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *     path="/api/inquiries/{id}",
     *     summary="Update an existing inquiry",
     *     tags={"Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inquiry to update",
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
     *         description="Inquiry updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inquiry updated successfully"),
     *             @OA\Property(property="inquiry", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found"
     *     )
     * )
     */
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

    
    public function update(Request $request, Inquiry $inquiry)
    {

        $validated = $request->validate([
            'inquiry_number' => 'sometimes|integer',
            'mobile_number' => 'sometimes|string',
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
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'nullable|integer',
            'offers_status' => 'nullable|integer',

            //offers
            'inquiry_id' => 'sometimes|exists:inquiries,id',
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

      
        $inquiry_date = !empty($request->inquiry_date) && strtotime($request->inquiry_date)
        ? \Carbon\Carbon::parse($request->inquiry_date)->format('Y-m-d') 
        : null;

        $first_contact_date = !empty($request->first_contact_date) && strtotime($request->first_contact_date)
        ? \Carbon\Carbon::parse($request->first_contact_date)->format('Y-m-d') 
        : null;

        $second_contact_date = !empty($request->second_contact_date) && strtotime($request->second_contact_date)
        ? \Carbon\Carbon::parse($request->second_contact_date)->format('Y-m-d') 
        : null;

        $third_contact_date = !empty($request->third_contact_date) && strtotime($request->third_contact_date)
        ? \Carbon\Carbon::parse($request->third_contact_date)->format('Y-m-d') 
        : null;

        

        // $inquiry->update([
        //     'inquiry_number' => $validated['inquiry_number'] ?? $inquiry->inquiry_number,
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

        $inquiry->inquiry_number = $validated['inquiry_number'] ?? $inquiry->inquiry_number;
        $inquiry->mobile_number = $validated['mobile_number'];
        $inquiry->inquiry_date = $inquiry_date;
        $inquiry->product_categories = $validated['product_categories'];
        $inquiry->specific_product = $validated['specific_product'];
        $inquiry->name = $validated['name'];
        $inquiry->location = $validated['location'];
        $inquiry->inquiry_through = $validated['inquiry_through'];
        $inquiry->inquiry_reference = $validated['inquiry_reference'];
        $inquiry->first_contact_date = $first_contact_date;
        $inquiry->first_response = $validated['first_response'];
        $inquiry->second_contact_date = $second_contact_date;
        $inquiry->second_response = $validated['second_response'];
        $inquiry->third_contact_date = $third_contact_date;
        $inquiry->third_response = $validated['third_response'];
        $inquiry->notes = $validated['notes'];
        $inquiry->status = $validated['status'];
        $inquiry->offers_status = $request->has('offers_status') ? $validated['offers_status'] : 2;

        // Check if any field was actually modified
        if ($inquiry->isDirty()) {
            $inquiry->user_id = $validated['user_id'];
        }

        $inquiry->save();


          // Offers
        

        $offerData = $request->input('offer_data', []);
        $communication_date = isset($offerData['communication_date']) 
        ? \Carbon\Carbon::parse($offerData['communication_date']) // Direct parsing
        : null;

        $sample_dispatched_date = isset($offerData['sample_dispatched_date']) 
            ? \Carbon\Carbon::parse($offerData['sample_dispatched_date']) 
            : null;

        $sample_received_date = isset($offerData['sample_received_date']) 
            ? \Carbon\Carbon::parse($offerData['sample_received_date']) 
            : null;    

        $offer = null;

        if ($inquiry->status == 1) {
            $existingOffer = \App\Models\Offer::where('inquiry_id', $inquiry->id)->first();

            if (!$existingOffer) {
                $lastOfferNumber = \App\Models\Offer::max('offer_number') ?? 0;
                $newOfferNumber = $lastOfferNumber + 1;
            }
            
            $offer = Offer::updateOrCreate(
                ['inquiry_id' => $inquiry->id],
                [
                    'offer_number' => $existingOffer->offer_number ?? $newOfferNumber,
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

            if ($inquiry->offers_status == 1) {
                $lastOrderNumber = \App\Models\Order::max('order_number') ?? 56564;
                $newOrderNumber = $lastOrderNumber + 1;
        
                \App\Models\Order::create([
                    'order_number' => $newOrderNumber,
                    'offer_id' => $offer->id,
                ]);
            }
        }else{
            $offer = null;
        }
        return response()->json([
            'message' => 'Inquiry updated successfully.',
            'inquiry' =>$inquiry,
            'offer' => $offer
        ], 200);
    }

    public function offerDomesticCancellations()
    {
        $offer_domestic_cancellations = Inquiry::with('user')
            ->where('status', 1)
            ->where('offers_status', 0)
            ->whereNotIn('mobile_number', function ($subquery) {
                $subquery->select('mobile_number')->from('blocked_domestic_offers');
            })
            ->get()
            ->map(function ($inquiry) {
                $offer = \App\Models\Offer::where('inquiry_id', $inquiry->id)->first();
                $inquiry->offer_number = $offer?->offer_number ?? null;
                return $inquiry;
            });

        return response()->json($offer_domestic_cancellations);
    }

    public function orderDomesticCancellations()
    {
        $combinedResults = collect();

        // Step 1: Get inquiries matching cancellation logic
        $inquiries = Inquiry::with('user')
            ->where('status', 1)
            ->where('offers_status', 1)
            ->where('orders_status', 0)
            ->whereNotIn('mobile_number', function ($subquery) {
                $subquery->select('mobile_number')->from('blocked_orders');
            })
            ->get()
            ->map(function ($inquiry) {
                $offers = \App\Models\Offer::where('inquiry_id', $inquiry->id)->get();

                $offers->map(function ($offer) {
                    $order = \App\Models\Order::with(['sellers','user'])->where('offer_id', $offer->id)->first();
                    $offer->order = $order;
                    return $offer;
                });

                $inquiry->offers = $offers;

                return $inquiry;
            });


        // Add to combined list
        $combinedResults = $combinedResults->merge($inquiries);

        // Step 2: Get standalone orders where status = 0
        $orders = \App\Models\Order::with(['sellers','user'])
            ->where('status', 0)
            ->get();

        Log::info($orders);

        // Add to combined list
        $combinedResults = $combinedResults->merge($orders);

        // Step 3: Return merged list as JSON
        return response()->json($combinedResults);
    }



    public function updateInquiryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|integer',
            'offers_status' => 'nullable|integer',
            'orders_status' => 'nullable|integer',
            'user_id' => 'required|exists:users,id',
        ]);

        $order = Order::find($id);

        // Step 1: If order exists and offer_id is null → update order only
        if ($order && is_null($order->offer_id)) {
            $order->status = $request->orders_status ?? $order->status;
            $order->user_id = $request->user_id ?? $order->user_id;
            $order->save();



            return response()->json([
                'success' => true,
                'message' => 'Standalone Order status updated successfully.',
                'responseMessage' => 'Order status updated (without inquiry).'
            ]);
            
        }



    
        $inquiry = Inquiry::find($id);
        if ($inquiry) {
            $inquiry->status = $request->status ?? $inquiry->status;
            $inquiry->offers_status = $request->offers_status ?? $inquiry->offers_status;
            $inquiry->orders_status = $request->orders_status ?? $inquiry->orders_status;
            $inquiry->user_id = $request->user_id ?? $inquiry->user_id;
            $inquiry->save();        

            $responseMessage = 'Status updated successfully.';
        
            if ($request->status == 0) {
                $responseMessage = 'Inquiry moved to Cancellations.';
            } elseif ($request->status == 1 && $inquiry->offers_status === 2) {
                $lastOfferNumber = \App\Models\Offer::max('offer_number') ?? 0;
                $newOfferNumber = $lastOfferNumber + 1;
        
                $offer = \App\Models\Offer::firstOrNew(['inquiry_id' => $inquiry->id]);
                $offer->offer_number = $newOfferNumber;
                $offer->inquiry_id = $inquiry->id;
                $offer->save();
        
                $responseMessage = 'Inquiry moved to Offers and offer number created.';
            } elseif ($request->status == 1 && $request->offers_status === 0) {
                $responseMessage = 'Inquiry moved to Offer Cancellations.';
            } elseif ($request->status == 1 && $request->offers_status === 1 && $request->orders_status ===0) {
                $responseMessage = 'Inquiry moved to Orders Cancellations.';
            } elseif ($request->status == 1 && $request->offers_status === 1) {
                $lastOrderNumber = \App\Models\Order::max('order_number');
                if ($lastOrderNumber === null || $lastOrderNumber < 56564) {
                    $lastOrderNumber = 56564;
                }
                $newOrderNumber = $lastOrderNumber + 1;
        
                $offer = \App\Models\Offer::where('inquiry_id', $inquiry->id)->first();
        
                if ($offer) {
                    $order = \App\Models\Order::firstOrNew(['offer_id' => $offer->id]);
                    $order->order_number = $newOrderNumber;
                    $order->offer_id = $offer->id;
                    $order->save();
        
                    $responseMessage = 'Inquiry moved to Orders and order number updated.';
                } else {
                    $responseMessage = 'Offer not found for the inquiry.';
                }
            }

            return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'responseMessage' => $responseMessage
        ]);

        }

        return response()->json([
            'success' => false,
            'message' => 'Record not found.'
        ], 404);

    
        
    }
    


    /**
     * @OA\Post(
     *     path="/api/inquiries/bulk-upload",
     *     summary="Bulk upload inquiries",
     *     description="Upload a CSV or TXT file to bulk import inquiries into the system.",
     *     operationId="bulkUpload",
     *     tags={"Inquiry"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="CSV or TXT file containing inquiries to upload.",
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
     *         description="Inquiries imported successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inquiries imported successfully.")
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

            $import = new InquiryImport();
            Excel::import($import, public_path($filePath));

            if (!empty($import->getErrors())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed with errors.',
                    'errors'  => $import->getErrors()
                ], 422);
            }
            
    
            
            // Store upload record in the database
            UploadInquiry::create([
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
        $uploadinquirydata = UploadInquiry::with('user:id,name')->get();
        return response()->json($uploadinquirydata);
    }

    public function uploadDestroy($id)
    {
        $uploadInquiry = UploadInquiry::find($id);
        if (!$uploadInquiry) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $uploadInquiry->delete();

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
        }, 'inquiry_template.xlsx');

    }
   


    /**
     * Remove the specified inquiry from storage.
     *
     * @param  \App\Models\Inquiry  $inquiry
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     *     path="/api/inquries/{id}",
     *     summary="Delete a inquiry",
     *     tags={"Inquiries"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inquiry to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inquiry deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found"
     *     )
     * )
     */
    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();
        return response()->json([
            'success' => true,
            'message' => 'Inquiry deleted successfully.',
        ], 200);
    }

    public function getNextInquiryNumber()
    {
        $nextNumber = \App\Models\Inquiry::max('inquiry_number') + 1;
        return response()->json(['next_inquiry_number' => $nextNumber]);
    }

    
}
