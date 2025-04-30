<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternationInquiry;
use App\Models\InternationalOrder;
use Carbon\Carbon;
use App\Models\InternationalOrderSeller;
use Illuminate\Support\Facades\Log;
use App\Rules\UniqueMobileAcrossTables;
use Spatie\Browsershot\Browsershot;


class InternationalOrderController extends Controller
{
    public function index()
    {
        $international_orders = \App\Models\InternationalOrder::with([       
                'international_sellers',                        
                'international_offer.international_inquiry'])->get();
    
        return response()->json($international_orders);
    }
    
    public function showByOrderId($id)
    {
        
        // $international_order = InternationalOrder::with('international_sellers')
        //             ->where('id', $id)
        //             ->first();

        $international_order = \App\Models\InternationalOrder::with([
            'international_sellers',
            'international_offer.international_inquiry'
        ])->where('id', $id)->first();
                
        if (!$international_order) {
            return response()->json(['error' => 'International Order not found'], 404);
        }


        return response()->json([
            'international_order' => $international_order,
            'international_sellers' => $international_order->international_sellers,
            'international_inquiry' => $international_order->international_offer->international_inquiry ?? null,
        ]);
    }

    public function update(Request $request, $international_offer_id)
    {
        $validatedData = $request->validate([
            'international_offer_id'  => 'required|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'contact_number' => 'string|max:20',
            'seller_assigned' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric',
            'seller_offer_rate' => 'nullable|numeric',
            'gst' => 'nullable|string|max:50',
            'buyer_offer_rate' => 'nullable|numeric',
            'final_shipping_value' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'buyer_gst_number' => 'nullable|string|max:100',
            'buyer_pan' => 'nullable|string|max:100',
            'buyer_bank_details' => 'nullable|string|max:255',
            'amount_received' => 'nullable|numeric',
            'amount_received_date' => 'nullable|date',
            'amount_paid' => 'nullable|numeric',
            'amount_paid_date' => 'nullable|date',
            'logistics_through' => 'nullable|string|max:100',
            'logistics_agency' => 'nullable|string|max:100',
            'shipping_estimate_value' => 'nullable|numeric',
            'buyer_final_shipping_value' => 'nullable|numeric',
    
            // Sellers array validation
            'international_sellers' => 'required|array|min:1',
            'international_sellers.*.seller_name' => 'nullable|string|max:255',
            'international_sellers.*.seller_address' => 'nullable|string|max:255',
            'international_sellers.*.seller_contact' => 'nullable|string|max:20',
            'international_sellers.*.shipping_name' => 'nullable|string|max:255',
            'international_sellers.*.address_line_1' => 'nullable|string|max:255',
            'international_sellers.*.address_line_2' => 'nullable|string|max:255',
            'international_sellers.*.seller_pincode' => 'nullable|string|max:20',
            'international_sellers.*.seller_contact_person_name' => 'nullable|string|max:255',
            'international_sellers.*.seller_contact_person_number' => 'nullable|string|max:20',
            'international_sellers.*.no_of_boxes' => 'nullable|numeric',
            'international_sellers.*.weight_per_unit' => 'nullable|numeric',
            'international_sellers.*.dimension_unit' => 'nullable|string|max:10',
            'international_sellers.*.length' => 'nullable|numeric',
            'international_sellers.*.width' => 'nullable|numeric',
            'international_sellers.*.height' => 'nullable|numeric',
            'international_sellers.*.invoice_generate_date' => 'nullable|date',
            'international_sellers.*.invoice_value' => 'nullable|numeric',
            'international_sellers.*.invoice_number' => 'nullable|string|max:100',
            'international_sellers.*.order_ready_date' => 'nullable|date',
            'international_sellers.*.order_delivery_date' => 'nullable|date',
            'international_sellers.*.order_dispatch_date' => 'nullable|date',
    
            // Invoice
            'international_sellers.*.invoicing_invoice_generate_date' => 'nullable|date',
            'international_sellers.*.invoicing_invoice_number' => 'nullable|string',
            'international_sellers.*.invoice_to' => 'nullable|string',
            'international_sellers.*.invoice_address' => 'nullable|string',
            'international_sellers.*.invoice_gstin' => 'nullable|string',
            'international_sellers.*.packaging_expenses' => 'nullable|numeric',
            'international_sellers.*.invoicing_total_amount' => 'nullable|numeric',
            'international_sellers.*.total_amount_in_words' => 'nullable|string',
            'international_sellers.*.product_name' => 'nullable|string',
            'international_sellers.*.rate_per_kg' => 'nullable|numeric',
            'international_sellers.*.total_kg' => 'nullable|numeric',
            'international_sellers.*.hsn' => 'nullable|string',
            'international_sellers.*.invoicing_amount' => 'nullable|numeric',
            'international_sellers.*.expenses' => 'nullable|numeric',
        ]);
    
        $international_order = InternationalOrder::where('international_offer_id', $request->international_offer_id)->first();

        $isNew = false;
    
        if ($international_order) {
            $international_order->update($validatedData);
        } else {
            $international_order = InternationalOrder::create($validatedData);
            $isNew = true;
        }

    
        InternationalOrderSeller::where('international_order_id', $international_order->id)->delete();
    
        foreach ($request->input('international_sellers') as $sellerData) {
            $sellerData['international_order_id'] = $international_order->id;    
            InternationalOrderSeller::create($sellerData);
        }
        
    
        return response()->json([
            'message' => $isNew ? 'Order created successfully' : 'Order updated successfully',
            'international_order' => $international_order,
            'international_sellers' => $international_order->international_sellers,
        ]);
    }



    public function generatePDF(Request $request)
    {
        $data = $request->all();
        $html = view('pdf.international_invoice', ['data' => $data])->render();
        $filename = "invoice_{$data['invoicing_invoice_number']}.pdf";
    
        $pdfContent = Browsershot::html($html)
            ->addChromiumArguments(['--no-sandbox'])
            ->format('A4')
            ->showBackground()
            ->pdf();
    
        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }


    

}
