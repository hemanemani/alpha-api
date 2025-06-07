<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;


class SellerController extends Controller
{
    public function index()
    {
        $sellers = Seller::all();
        return response()->json($sellers);
    }
    public function show($id)
    {
        $seller = Seller::with('products')->findOrFail($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'seller' => $seller,
        ], 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'location' => 'nullable|string',
            'mobile_number' => 'required|string|max:255',
            'email' => 'nullable|email|unique:sellers,email',
            'gst' => 'nullable|string|max:255',
            'pan' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:255',
            'status' => 'nullable',
            
            'products' => 'nullable|array',
            'products.*.name' => 'nullable|string',
            'products.*.seller_price' => 'nullable|numeric',
            'products.*.price' => 'nullable|numeric',
            'products.*.variety' => 'nullable|string',
            'products.*.moq' => 'nullable|string',
            'products.*.remarks' => 'nullable|string',
            'products.*.rate' => 'nullable|numeric',
    
        ]);

        try {

            $seller = Seller::create([
                'name' => $validated['name'],
                'company_name' => $validated['company_name'] ?? null,
                'mobile_number' => $validated['mobile_number'],
                'type' => $validated['type'] ?? null,
                'location' => $validated['location'] ?? null,
                'email' => $validated['email'] ?? null,
                'gst' => $validated['gst'] ?? null,
                'pan' => $validated['pan'] ?? null,
                'bank_details' => $validated['bank_details'] ?? null,
                'pickup_address' => $validated['pickup_address'] ?? null,
                'status' => $validated['status'] ?? null,       
                
            ]);

            if($seller){
                foreach ($validated['products'] as $productData) {
                    $seller->products()->create($productData);
                } 
            }

            return response()->json([
                'success' => true,
                'message' => 'Seller and Products Created Successfully',
                'seller' => $seller
            ], 201);   
        }catch (\Exception $e) {    
            return response()->json([
                'success' => false,
                'message' => 'Failed to create seller and products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, Seller $seller)
    {

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'location' => 'nullable|string',
            'mobile_number' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:sellers,email,' . $seller->id,
            'gst' => 'nullable|string|max:255',
            'pan' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:255',
            'status' => 'nullable',

            'products' => 'nullable|array',
            'products.*.name' => 'nullable|string',
            'products.*.seller_price' => 'nullable|numeric',
            'products.*.price' => 'nullable|numeric',
            'products.*.variety' => 'nullable|string',
            'products.*.moq' => 'nullable|string',
            'products.*.remarks' => 'nullable|string',
            'products.*.rate' => 'nullable|numeric',
        ]);

        try{
            $seller->update([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile_number' => $request->mobile_number,
                'type' => $request->type,
                'location' => $request->location,
                'email' => $request->email,
                'gst' => $request->gst,
                'pan'  => $request->pan,
                'bank_details' => $request->bank_details,
                'pickup_address' => $request->pickup_address,
                'status' => $request->status,
            ]);

            if ($request->has('products')) {
                $seller->products()->delete();
                foreach ($request->products as $productData) {
                    $seller->products()->create($productData);
                }
            }
    

            return response()->json([
                'success' => true,
                'message' => 'Seller and Products Created Successfully',
                'seller' => $seller->load('products'),
            ], 201);
        }
        catch (\Exception $e) {    
            return response()->json([
                'success' => false,
                'message' => 'Failed to create seller and products',
                'error' => $e->getMessage()
            ], 500);
        }

    }
    public function destroy(Seller $seller)
    {
        $seller->delete();
        return response()->json([
            'success' => true,
            'message' => 'Seller deleted successfully.',
        ], 200);
    }
    public function getSellers()
    {
        $sellers = Seller::select('id', 'name','pickup_address','mobile_number')->get();
        return response()->json($sellers);
    }

}
