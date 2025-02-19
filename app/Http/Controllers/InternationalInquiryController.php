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
        $inquiries = InternationInquiry::with('user')->where('status','2')->get();
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

    public function approved_offers()
    {
        $approved_offers = InternationInquiry::where('status', 1)
        ->whereNull('offers_status')
        ->get();
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
        $cancelled_offers = InternationInquiry::where('status', 0)
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


    public function store(Request $request)
    {
        $validated = $request->validate([
            'inquiry_number' => 'required',
            'mobile_number' => 'required',
            'inquiry_date' => 'required|date',
            'product_categories' => 'required|string',
            'specific_product' => 'required|string',
            'name' => 'required|string',
            'location' => 'required|string',
            'inquiry_through' => 'required|string',
            'inquiry_reference' => 'required|string',
            'first_contact_date' => 'required|date',
            'first_response' => 'required|string',
            'second_contact_date' => 'nullable|date',
            'second_response' => 'nullable|string',
            'third_contact_date' => 'nullable|date',
            'third_response' => 'nullable|string',
            'notes' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => "required|boolean"

        ]);

        if (BlockedInternationalInquiry::where('mobile_number', $request->mobile_number)->exists() || BlockedInternationalOffer::where('mobile_number', $request->mobile_number)->exists() ) {
            return response()->json(['message' => 'This inquiry is blocked.'], 403);
        }

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
            'notes' => $validated['notes'],
            'user_id' => $validated['user_id'],
            'status' => $validated['status'],

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
            'mobile_number' => 'sometimes|string',
            'inquiry_date' => 'sometimes|date',
            'product_categories' => 'sometimes|string',
            'specific_product' => 'sometimes|string',
            'name' => 'sometimes|string',
            'location' => 'sometimes|string',
            'inquiry_through' => 'sometimes|string',
            'inquiry_reference' => 'sometimes|string',
            'first_contact_date' => 'sometimes|date',
            'first_response' => 'sometimes|string',
            'second_contact_date' => 'nullable|date',
            'second_response' => 'nullable|string',
            'third_contact_date' => 'nullable|date',
            'third_response' => 'nullable|string',
            'notes' => 'nullable|string',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|integer',

            //offers
            'international_inquiry_id' => 'sometimes|exists:international_inquiries,id',
            'offer_number' => 'sometimes|string',
            'communication_date' => 'sometimes|date',
            'received_sample_amount' => 'sometimes|integer',
            'sample_dispatched_date' => 'sometimes|date',
            'sample_sent_through' => 'sometimes|string',
            'sample_received_date' => 'sometimes|date',
            'offer_notes' => 'sometimes|string',
            

        ]);

        $inquiry_date = $this->parseDate($request->inquiry_date);
        $first_contact_date = $this->parseDate($request->first_contact_date);
        $second_contact_date = $this->parseDate($request->second_contact_date);
        $third_contact_date = $this->parseDate($request->third_contact_date);
    
        // Offers
        $communication_date = $this->parseDate($request->communication_date);
        $sample_dispatched_date = $this->parseDate($request->sample_dispatched_date);
        $sample_received_date = $this->parseDate($request->sample_received_date);

        $international_inquiry->update([
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
            'status' => $validated['status'],

        ]);

        $offerData = $request->input('offer_data', []);

        $international_offer = null;
        if ($international_inquiry->status == 1 && isset($offerData['offer_number'])) {
            $international_offer = InternationalOffer::updateOrCreate(
                ['international_inquiry_id' => $international_inquiry->id],
                [
                    'offer_number' => $offerData['offer_number'],
                    'communication_date' => $offerData['communication_date'] ?? null,
                    'received_sample_amount' => $offerData['received_sample_amount'] ?? null,
                    'sample_dispatched_date' => $offerData['sample_dispatched_date'] ?? null,
                    'sample_sent_through' => $offerData['sample_sent_through'] ?? null,
                    'sample_received_date' => $offerData['sample_received_date'] ?? null,
                    'offer_notes' => $offerData['offer_notes'] ?? null,
                ]
            );
        }else {
            $international_offer = null;
        }
    

        return response()->json([
            'message' => 'Inquiry updated successfully.',
            'international_inquiry' =>$international_inquiry,
            'international_offer' => $international_offer
        ], 200);

    }

    public function offerInternationalCancellations()
    {

        $offer_international_cancellations = InternationInquiry::where('status', 1)
            ->where('offers_status', 0)
            ->whereNotIn('mobile_number', function ($subquery) {
                $subquery->select('mobile_number')->from('blocked_international_offers');
            })
            ->get();
    
        return response()->json($offer_international_cancellations);

    }

    public function updateInternationInquiryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer',
        ]);

        $inquiry = InternationInquiry::findOrFail($id);
        $inquiry->status = $request->status;
        $inquiry->save();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry status updated successfully.'
        ]);
    }

    public function updateInternationalOfferStatus(Request $request, $id)
    {
        $request->validate([
            'offers_status' => 'nullable|boolean',
        ]);
        $international_inquiry = InternationInquiry::findOrFail($id);

        if($international_inquiry->status == 1){
            $international_inquiry->offers_status = $request->offers_status;
            $international_inquiry->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Offer status updated successfully.',
                'offers_status' => $international_inquiry->offers_status,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Offer status updated successfully.',
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
    
            Excel::import(new InternationalInquiryImport, $request->file('file'));
            
            return response()->json([
                'success' => true,
                'message' => 'International Inquiries imported successfully.',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'There was an error during import.',
                'error' => $e->getMessage(),
            ], 500);        }
    }
    
    
    public function downloadTemplate()
    {
        $headers = [
            'inquiry_number',
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
     * @param  \App\Models\InternationInquiry  $inquiry
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
}
