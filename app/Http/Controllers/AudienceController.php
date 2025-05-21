<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audience;

class AudienceController extends Controller
{
    public function index()
    {
        return Audience::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255|unique:audiences,value',
        ]);

        return Audience::create($data);
    }

    public function destroy($id)
    {
        $audience = Audience::findOrFail($id);
        $audience->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

}
