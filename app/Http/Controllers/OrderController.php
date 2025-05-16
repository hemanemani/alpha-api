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
        $orders = \App\Models\Order::with([       
                'sellers',                         
                'offer.inquiry.user',
                'user'
            ])
            ->where('status', 2)
            ->orderBy('id', 'desc')
            ->where(function($query) {
                $query->whereHas('sellers')
                    ->orWhereHas('offer.inquiry', function ($subQuery) {
                        $subQuery->where('orders_status',2);
                    });
            })
            ->get();
            

        return response()->json($orders);
    }


    public function showByOrderId($id)
    {
        $order = \App\Models\Order::with([
            'sellers',
            'offer.inquiry.user',
            'user'
        ])->where('id', $id)->first();
                
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }


        return response()->json([
            'order' => $order,
            'sellers' => $order->sellers,
            'inquiry' => $order->offer->inquiry ?? null,
        ]);

    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'offer_id'  => 'nullable|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'mobile_number' => 'string|max:20',
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
            'user_id' => 'required|exists:users,id',

    
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
            'sellers.*.order_delivery_date' => 'nullable|date',
            'sellers.*.order_dispatch_date' => 'nullable|date',

    
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
    
        $orderData = collect($validatedData)->except('sellers')->toArray();
        
        $order = Order::create($orderData);
    
        OrderSeller::where('order_id', $order->id)->delete();
    
        foreach ($validatedData['sellers'] as $sellerData) {
            $sellerData['order_id'] = $order->id;
            OrderSeller::create($sellerData);
        }
    
    
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
            'sellers' => $order->sellers,
        ]);
    }

    public function update(Request $request, $offer_id)
    {
        $validatedData = $request->validate([
            'offer_id'  => 'nullable|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
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
            'user_id' => 'required|exists:users,id',

    
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
            'sellers.*.order_delivery_date' => 'nullable|date',
            'sellers.*.order_dispatch_date' => 'nullable|date',

    
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

        if ($request->has('offer_id') && $request->offer_id) {
            $order = Order::where('offer_id', $request->offer_id)->first();
        } else {
            $order = Order::where('id', $request->id)->first();
        }

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $order->fill($validatedData);
        $orderIsDirty = $order->isDirty();
            
        if ($orderIsDirty) {

            $orderData = collect($validatedData)->except('sellers')->toArray();
            $order->update($orderData);

        } 
    
        OrderSeller::where('order_id', $order->id)->delete();
    
        foreach ($request->input('sellers') as $sellerData) {
            $sellerData['order_id'] = $order->id;
            OrderSeller::create($sellerData);
        }
        
    
        return response()->json([
            'message' => 'Order updated successfully',
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

    public function getNextOrderNumber()
    {
        $lastNumber = \App\Models\Order::max('order_number');
        if ($lastNumber === null || $lastNumber < 56564) {
            $lastNumber = 56564;
        }
        $nextNumber = $lastNumber + 1;
        return response()->json(['next_order_number' => $nextNumber]);
    }
    

}
