<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(title="My API", version="1.0")
 */
class ApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/example",
     *     summary="Example API",
     *     @OA\Response(response=200, description="Successful response")
     * )
     */
    public function example(Request $request)
    {
        return response()->json(['message' => 'This is an example response.']);
    }
}
