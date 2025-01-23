<?php

namespace App\Http\Controllers;

use App\Models\InternationInquiry;
use Illuminate\Http\Request;
use App\Imports\InternationalInquiryImport;
use Maatwebsite\Excel\Facades\Excel;

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
        $inquiries = InternationInquiry::with('user')->whereNull('status')->get();
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
        $approved_offers = InternationInquiry::where('status', 1)->get();
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
        $cancelled_offers = InternationInquiry::where('status', 0)->get();
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
    public function update(Request $request, InternationInquiry $international_inquiry)
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
        ]);

        return response()->json([
            'message' => 'International Inquiry updated successfully.',
            'international_inquiry' =>$international_inquiry
        ], 200);
    }

    public function updateInternationInquiryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $inquiry = InternationInquiry::findOrFail($id);
        $inquiry->status = $request->status;
        $inquiry->save();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry status updated successfully.'
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
