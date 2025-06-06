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
use Barryvdh\DomPDF\Facade\Pdf;


class OrderController extends Controller
{
   public function index()
    {
        $orders = \App\Models\Order::with([       
                'order_sellers',                         
                'offer.inquiry.user',
                'user'
            ])
            ->where('status', 2)
            ->orderBy('id', 'desc')
            ->where(function($query) {
                $query->whereHas('order_sellers')
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
            'order_sellers',
            'offer.inquiry.user',
            'user'
        ])->where('id', $id)->first();
                
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order && is_string($order->sellerdetails)) {
            $order->sellerdetails = json_decode($order->sellerdetails, true);
        }

        return response()->json([
            'order' => $order,
            'sellers' => $order->order_sellers,
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
            'buyer_gst_number' => 'nullable|string|max:100',
            'buyer_pan' => 'nullable|string|max:100',
            'buyer_bank_details' => 'nullable|string|max:255',
            'amount_received' => 'nullable|numeric',
            'amount_received_date' => 'nullable|date',
            'shipping_estimate_value' => 'nullable|numeric',
            'buyer_final_shipping_value' => 'nullable|numeric',
            'buyer_amount' => "nullable|numeric",
            'buyer_total_amount' => "nullable|numeric",
            'user_id' => 'required|exists:users,id',
            'products' => 'array|required',
            'products.*.seller_assigned' => 'nullable|numeric|sometimes',
            'products.*.product_name' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric',
            'products.*.seller_offer_rate' => 'nullable|numeric',
            'products.*.gst' => 'nullable|numeric',
            'products.*.buyer_offer_rate' => 'nullable|numeric',
            'products.*.buyer_order_amount' => 'nullable|numeric',
            'products.*.hsn' => 'nullable|string',
            'products.*.rate_per_kg' => 'nullable|numeric',
            'products.*.total_kg' => 'nullable|numeric',
            'products.*.product_total_amount' => 'nullable|numeric',


    
            // Sellers array validation
            'order_sellers' => 'required|array|min:1',
            'order_sellers.*.seller_name' => 'nullable|string|max:255',
            'order_sellers.*.seller_address' => 'nullable|string|max:255',
            'order_sellers.*.seller_contact' => 'nullable|string|max:20',
            'order_sellers.*.shipping_name' => 'nullable|string|max:255',
            'order_sellers.*.address_line_1' => 'nullable|string|max:255',
            'order_sellers.*.address_line_2' => 'nullable|string|max:255',
            'order_sellers.*.seller_pincode' => 'nullable|string|max:20',
            'order_sellers.*.seller_contact_person_name' => 'nullable|string|max:255',
            'order_sellers.*.seller_contact_person_number' => 'nullable|string|max:20',
            'order_sellers.*.no_of_boxes' => 'nullable|numeric',
            'order_sellers.*.weight_per_unit' => 'nullable|numeric',
            'order_sellers.*.dimension_unit' => 'nullable|string|max:10',
            'order_sellers.*.length' => 'nullable|numeric',
            'order_sellers.*.width' => 'nullable|numeric',
            'order_sellers.*.height' => 'nullable|numeric',
            'order_sellers.*.invoice_generate_date' => 'nullable|date',
            'order_sellers.*.invoice_value' => 'nullable|numeric',
            'order_sellers.*.invoice_number' => 'nullable|string|max:100',
            'order_sellers.*.delivery_address' => 'nullable|string|max:100',
            'order_sellers.*.order_ready_date' => 'nullable|date',
            'order_sellers.*.order_delivery_date' => 'nullable|date',
            'order_sellers.*.order_dispatch_date' => 'nullable|date',
            'order_sellers.*.amount_paid' => 'nullable|numeric',
            'order_sellers.*.amount_paid_date' => 'nullable|date',
            'order_sellers.*.logistics_through' => 'nullable|string|max:100',
            'order_sellers.*.logistics_agency' => 'nullable|string|max:100',

    
            // Invoice
            'order_sellers.*.invoicing_invoice_generate_date' => 'nullable|date',
            'order_sellers.*.invoicing_invoice_number' => 'nullable|string',
            'order_sellers.*.invoice_to' => 'nullable|string',
            'order_sellers.*.invoice_address' => 'nullable|string',
            'order_sellers.*.invoice_gstin' => 'nullable|string',
            'order_sellers.*.packaging_expenses' => 'nullable|numeric',
            'order_sellers.*.invoicing_total_amount' => 'nullable|numeric',
            'order_sellers.*.total_amount_in_words' => 'nullable|string',
            'order_sellers.*.invoicing_amount' => 'nullable|numeric',
            'order_sellers.*.expenses' => 'nullable|numeric',
            

        ]);

        $request->validate([
            'mobile_number' => ['required', new UniqueMobileAcrossTables],
        ]);
    
        $orderData = collect($validatedData)->except('order_sellers')->toArray();
        $orderData['sellerdetails'] = json_encode($validatedData['products']);

        
        $order = Order::create($orderData);
    
        // OrderSeller::where('order_id', $order->id)->delete();
    
        foreach ($validatedData['order_sellers'] as $sellerData) {
            $sellerData['order_id'] = $order->id;
            OrderSeller::create($sellerData);
        }
    
    
        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
            'order_sellers' => $order->order_sellers,
        ]);
    }

    

    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'offer_id'  => 'nullable|numeric',
            'order_number' => 'nullable|numeric',
            'name' => 'nullable|string|max:255',
            'mobile_number' => ['required', 'string', new UniqueMobileAcrossTables($order->id)],
            'buyer_gst_number' => 'nullable|string|max:100',
            'buyer_pan' => 'nullable|string|max:100',
            'buyer_bank_details' => 'nullable|string|max:255',
            'amount_received' => 'nullable|numeric',
            'amount_received_date' => 'nullable|date',
            'shipping_estimate_value' => 'nullable|numeric',
            'buyer_final_shipping_value' => 'nullable|numeric',
            'buyer_amount' => "nullable|numeric",
            'buyer_total_amount' => "nullable|numeric",
            'user_id' => 'required|exists:users,id',
            'products' => 'array|required',
            'products.*.seller_assigned' => 'nullable|numeric|sometimes',
            'products.*.product_name' => 'nullable|string',
            'products.*.quantity' => 'nullable|numeric',
            'products.*.seller_offer_rate' => 'nullable|numeric',
            'products.*.gst' => 'nullable|numeric',
            'products.*.buyer_offer_rate' => 'nullable|numeric',
            'products.*.hsn' => 'nullable|string',
            'products.*.rate_per_kg' => 'nullable|numeric',
            'products.*.total_kg' => 'nullable|numeric',
            'products.*.product_total_amount' => 'nullable|numeric',


    
            // Sellers array validation
            'order_sellers' => 'required|array|min:1',
            'order_sellers.*.seller_name' => 'nullable|string|max:255',
            'order_sellers.*.seller_address' => 'nullable|string|max:255',
            'order_sellers.*.seller_contact' => 'nullable|string|max:20',
            'order_sellers.*.shipping_name' => 'nullable|string|max:255',
            'order_sellers.*.address_line_1' => 'nullable|string|max:255',
            'order_sellers.*.address_line_2' => 'nullable|string|max:255',
            'order_sellers.*.seller_pincode' => 'nullable|string|max:20',
            'order_sellers.*.seller_contact_person_name' => 'nullable|string|max:255',
            'order_sellers.*.seller_contact_person_number' => 'nullable|string|max:20',
            'order_sellers.*.no_of_boxes' => 'nullable|numeric',
            'order_sellers.*.weight_per_unit' => 'nullable|numeric',
            'order_sellers.*.dimension_unit' => 'nullable|string|max:10',
            'order_sellers.*.length' => 'nullable|numeric',
            'order_sellers.*.width' => 'nullable|numeric',
            'order_sellers.*.height' => 'nullable|numeric',
            'order_sellers.*.invoice_generate_date' => 'nullable|date',
            'order_sellers.*.invoice_value' => 'nullable|numeric',
            'order_sellers.*.invoice_number' => 'nullable|string|max:100',
            'order_sellers.*.delivery_address' => 'nullable|string|max:100',
            'order_sellers.*.order_ready_date' => 'nullable|date',
            'order_sellers.*.order_delivery_date' => 'nullable|date',
            'order_sellers.*.order_dispatch_date' => 'nullable|date',
            'order_sellers.*.amount_paid' => 'nullable|numeric',
            'order_sellers.*.amount_paid_date' => 'nullable|date',
            'order_sellers.*.logistics_through' => 'nullable|string|max:100',
            'order_sellers.*.logistics_agency' => 'nullable|string|max:100',


    
            // Invoice
            'order_sellers.*.invoicing_invoice_generate_date' => 'nullable|date',
            'order_sellers.*.invoicing_invoice_number' => 'nullable|string',
            'order_sellers.*.invoice_to' => 'nullable|string',
            'order_sellers.*.invoice_address' => 'nullable|string',
            'order_sellers.*.invoice_gstin' => 'nullable|string',
            'order_sellers.*.packaging_expenses' => 'nullable|numeric',
            'order_sellers.*.invoicing_total_amount' => 'nullable|numeric',
            'order_sellers.*.total_amount_in_words' => 'nullable|string',
            'order_sellers.*.invoicing_amount' => 'nullable|numeric',
            'order_sellers.*.expenses' => 'nullable|numeric',

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

        $order['sellerdetails'] = json_encode($validatedData['products']);

        $orderIsDirty = $order->isDirty();
            
        if ($orderIsDirty) {
            $order->save();
        } 

    
    
        if ($request->has('order_sellers') && is_array($request->order_sellers)) {
            foreach ($request->input('order_sellers') as $sellerData) {
                    $sellerData['order_id'] = $order->id;

                    if (!empty($sellerData['id'])) {
                        $existingSeller = OrderSeller::where('id', $sellerData['id'])
                            ->where('order_id', $order->id)
                            ->first();

                        if ($existingSeller) {
                            $existingSeller->update($sellerData);
                        }
                    } else {
                        OrderSeller::create($sellerData);
                    }
                }

        } else {
            Log::warning('No order_sellers found in request or not an array.');
        }





        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order,
            'order_sellers' => $order->order_sellers,
        ]);
    }



    public function generatePDF(Request $request)
    {
        $data = $request->all();

        $filename = "invoice_{$data['invoicing_invoice_number']}.pdf";

        $pdf = Pdf::loadView('pdf.invoice', ['data' => $data])
                ->setPaper('A4', 'portrait');

        return $pdf->download($filename);
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
