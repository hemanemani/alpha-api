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
use Barryvdh\DomPDF\Facade\Pdf;


class InternationalOrderController extends Controller
{
    public function index()
    {
        $international_orders = \App\Models\InternationalOrder::with([
            'international_sellers',
            'international_offer.international_inquiry.user',
            'user'
        ])
        ->where('status', 2)
        ->orderBy('id', 'desc')
        ->where(function($query) {
            $query->whereHas('international_sellers')
                  ->orWhereHas('international_offer.international_inquiry', function ($subQuery) {
                      $subQuery->where('orders_status', 2);
                  });
        })
        ->get();
    
        return response()->json($international_orders);
    
    }

    
    public function showByOrderId($id)
    {
        
        // $international_order = InternationalOrder::with('international_sellers')
        //             ->where('id', $id)
        //             ->first();

        $international_order = \App\Models\InternationalOrder::with([
            'international_sellers',
            'international_offer.international_inquiry.user',
            'user'
        ])->where('id', $id)->first();
                
        if (!$international_order) {
            return response()->json(['error' => 'International Order not found'], 404);
        }

         if ($international_order && is_string($international_order->sellerdetails)) {
            $international_order->sellerdetails = json_decode($international_order->sellerdetails, true);
        }

        foreach ($international_order->international_sellers as $international_seller) {
        if (is_string($international_seller->products)) {
            $international_seller->products = json_decode($international_seller->products, true);
        }
        }


        return response()->json([
            'international_order' => $international_order,
            'international_sellers' => $international_order->international_sellers,
            'international_inquiry' => $international_order->international_offer->international_inquiry ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'international_offer_id'  => 'nullable|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'seller_assigned' => 'nullable|string|max:255',
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
            'buyer_total_amount' => "nullable|numeric",
            'user_id' => 'required|exists:users,id',
            'sellerdetails' => 'array|required',
            'sellerdetails.*.seller_name' => 'required|string',
            'sellerdetails.*.quantity' => 'required|string',
            'sellerdetails.*.seller_offer_rate' => 'required|numeric',
            'sellerdetails.*.gst' => 'required|string',
            'sellerdetails.*.buyer_offer_rate' => 'required|numeric',
            'sellerdetails.*.final_shipping_value' => 'required|string',
            'sellerdetails.*.total_amount' => 'required|numeric',

    
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
            'international_sellers.*.delivery_address' => 'nullable|string|max:100',
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
            'international_sellers.*.invoicing_amount' => 'nullable|numeric',
            'international_sellers.*.expenses' => 'nullable|numeric',

            // seller products
            'international_sellers.*.products' => 'required|array|min:1',
            'international_sellers.products.*.product_name' => 'nullable|string',
            'international_sellers.products.*.hsn' => 'nullable|string',
            'international_sellers.products.*.rate_per_kg' => 'nullable|string',
            'international_sellers.products.*.total_kg' => 'nullable|string',
            'international_sellers.products.*.product_total_amount' => 'nullable|numeric',
        ]);
    
        $orderData = collect($validatedData)->except('international_sellers')->toArray();
        $orderData['sellerdetails'] = json_encode($validatedData['sellerdetails']);

        $order = InternationalOrder::create($orderData);
    
        InternationalOrderSeller::where('international_order_id', $order->id)->delete();
    
        foreach ($validatedData['international_sellers'] as $sellerData) {
            $products = $sellerData['products'] ?? [];
            $sellerData['international_order_id'] = $order->id;
            $sellerData['products'] = json_encode($products);
            InternationalOrderSeller::create($sellerData);
        }
    
    
        return response()->json([
            'message' => 'International Order created successfully',
            'order' => $order,
            'international_sellers' => $order->international_sellers,
        ]);
    }

    public function update(Request $request, $international_offer_id)
    {
        $validatedData = $request->validate([
            'international_offer_id'  => 'nullable|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'seller_assigned' => 'nullable|string|max:255',
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
            'buyer_total_amount' => "nullable|numeric",
            'user_id' => 'required|exists:users,id',
            'sellerdetails' => 'array|required',
            'sellerdetails.*.seller_name' => 'required|string',
            'sellerdetails.*.quantity' => 'required|string',
            'sellerdetails.*.seller_offer_rate' => 'required|numeric',
            'sellerdetails.*.gst' => 'required|string',
            'sellerdetails.*.buyer_offer_rate' => 'required|numeric',
            'sellerdetails.*.final_shipping_value' => 'required|string',
            'sellerdetails.*.total_amount' => 'required|numeric',
    
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
            'international_sellers.*.delivery_address' => 'nullable|string|max:100',
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
            'international_sellers.*.invoicing_amount' => 'nullable|numeric',
            'international_sellers.*.expenses' => 'nullable|numeric',

            // seller products

            'international_sellers.*.products' => 'required|array|min:1',
            'international_sellers.products.*.product_name' => 'nullable|string',
            'international_sellers.products.*.hsn' => 'nullable|string',
            'international_sellers.products.*.rate_per_kg' => 'nullable|string',
            'international_sellers.products.*.total_kg' => 'nullable|string',
            'international_sellers.products.*.product_total_amount' => 'nullable|numeric',
        ]);

        if ($request->has('international_offer_id') && $request->international_offer_id) {
            $international_order = InternationalOrder::where('international_offer_id', $request->international_offer_id)->first();

        } else {
            $international_order = InternationalOrder::where('id', $request->id)->first();
        }

        if (!$international_order) {
            return response()->json([
                'message' => 'International Order not found',
            ], 404);
        }
    

        $international_order->fill($validatedData);
        $orderIsDirty = $international_order->isDirty();
            
        if ($orderIsDirty) {
            $orderData = collect($validatedData)->except('international_sellers')->toArray();
            $orderData['sellerdetails'] = json_encode($validatedData['sellerdetails']);
            $international_order->update($orderData);
        } 
    
    
        InternationalOrderSeller::where('international_order_id', $international_order->id)->delete();
    
        foreach ($request->input('international_sellers') as $sellerData) {
            $products = $sellerData['products'] ?? [];
            $sellerData['international_order_id'] = $international_order->id; 
            $sellerData['products'] = json_encode($products);   
            InternationalOrderSeller::create($sellerData);
        }
        
    
        return response()->json([
            'message' => 'International Order updated successfully',
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

    public function getNextInternationalOrderNumber()
    {
        $lastNumber = \App\Models\InternationalOrder::max('order_number');
        if ($lastNumber === null || $lastNumber < 56564) {
            $lastNumber = 56564;
        }
        $nextNumber = $lastNumber + 1;
        return response()->json(['next_order_number' => $nextNumber]);
    }

    

}
