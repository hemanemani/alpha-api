<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;


class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('seller:id,name')->get();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

}
