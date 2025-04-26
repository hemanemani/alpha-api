<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\OrderSeller;
use Illuminate\Support\Facades\Log;
use App\Rules\UniqueMobileAcrossTables;
use Spatie\Browsershot\Browsershot;


class OrderController extends Controller
{
    public function index()
{
    // Fetch inquiries with the necessary filters
    $orders = Inquiry::where('status', 1)
        ->where('offers_status', 1)
        ->get()
        ->map(function ($inquiry) {
            // Get related offers based on inquiry_id
            $offers = \App\Models\Offer::where('inquiry_id', $inquiry->id)->get();

            // For each offer, find the related order and sellers
            $offers->map(function ($offer) {
                // Find order using offer_id and eager load sellers
                $order = \App\Models\Order::with('sellers')->where('offer_id', $offer->id)->first();
                $offer->order = $order;
                return $offer;
            });

            // Attach offers to the inquiry
            $inquiry->offers = $offers;

            return $inquiry;
        });

    return response()->json($orders);
}

    
    public function showByOfferId($offer_id)
    {
        $order = Order::with('sellers')
                    ->where('offer_id', $offer_id)
                    ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'order' => $order,
            'sellers' => $order->sellers,
        ]);
    }

    public function update(Request $request, $offerId)
    {
        $validatedData = $request->validate([
            'offer_id'  => 'required|string',
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
            'sellers' => 'required|array|min:1',
            'sellers.*.seller_name' => 'nullable|string|max:255',
            'sellers.*.seller_address' => 'nullable|string|max:255',
            'sellers.*.seller_contact' => 'nullable|string|max:20',
            'sellers.*.shipping_name' => 'nullable|string|max:255',
            'sellers.*.address_line_1' => 'nullable|string|max:255',
            'sellers.*.address_line_2' => 'nullable|string|max:255',
            'sellers.*.seller_pincode' => 'nullable|string|max:20',
            'sellers.*.seller_contact_person_name' => 'nullable|string|max:255',
            'sellers.*.seller_contact_person_number' => 'nullable|string|max:20',
            'sellers.*.no_of_boxes' => 'nullable|numeric',
            'sellers.*.weight_per_unit' => 'nullable|numeric',
            'sellers.*.dimension_unit' => 'nullable|string|max:10',
            'sellers.*.length' => 'nullable|numeric',
            'sellers.*.width' => 'nullable|numeric',
            'sellers.*.height' => 'nullable|numeric',
            'sellers.*.invoice_generate_date' => 'nullable|date',
            'sellers.*.invoice_value' => 'nullable|numeric',
            'sellers.*.invoice_number' => 'nullable|string|max:100',
            'sellers.*.order_ready_date' => 'nullable|date',
    
            // Invoice
            'sellers.*.invoicing_invoice_generate_date' => 'nullable|date',
            'sellers.*.invoicing_invoice_number' => 'nullable|string',
            'sellers.*.invoice_to' => 'nullable|string',
            'sellers.*.invoice_address' => 'nullable|string',
            'sellers.*.invoice_gstin' => 'nullable|string',
            'sellers.*.packaging_expenses' => 'nullable|numeric',
            'sellers.*.invoicing_total_amount' => 'nullable|numeric',
            'sellers.*.total_amount_in_words' => 'nullable|string',
            'sellers.*.product_name' => 'nullable|string',
            'sellers.*.rate_per_kg' => 'nullable|numeric',
            'sellers.*.total_kg' => 'nullable|numeric',
            'sellers.*.hsn' => 'nullable|string',
            'sellers.*.invoicing_amount' => 'nullable|numeric',
            'sellers.*.expenses' => 'nullable|numeric',
        ]);
    
        $order = Order::where('offer_id', $request->offer_id)->first();

        $isNew = false;
    
        if ($order) {
            $order->update($validatedData);
        } else {
            $order = Order::create($validatedData);
            $isNew = true;
        }

        // $request->validate([
        //     'contact_number' => ['required', new UniqueMobileAcrossTables],
        // ]);
    
        OrderSeller::where('order_id', $order->id)->delete();
    
        foreach ($request->input('sellers') as $sellerData) {
            $sellerData['order_id'] = $order->id;
        
            // if (!empty($sellerData['invoice_generate_date'])) {
            //     $sellerData['invoice_generate_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $sellerData['invoice_generate_date'])->format('Y-m-d');
            // }
            // if (!empty($sellerData['order_ready_date'])) {
            //     $sellerData['order_ready_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $sellerData['order_ready_date'])->format('Y-m-d');
            // }
            // if (!empty($sellerData['invoicing_invoice_generate_date'])) {
            //     $sellerData['invoicing_invoice_generate_date'] = \Carbon\Carbon::createFromFormat('d-m-Y', $sellerData['invoicing_invoice_generate_date'])->format('Y-m-d');
            // }
        
            OrderSeller::create($sellerData);
        }
        
    
        return response()->json([
            'message' => $isNew ? 'Order created successfully' : 'Order updated successfully',
            'order' => $order,
            'sellers' => $order->sellers,
        ]);
    }



    public function generatePDF(Request $request)
    {
        $data = $request->all();
        $html = view('pdf.invoice', ['data' => $data])->render();
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
