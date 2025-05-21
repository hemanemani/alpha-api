<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ad;
use App\Models\InternationalAd;


class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::orderBy('created_at', 'desc')->get();
        return response()->json($ads);
    }
    public function international_index()
    {
        $ads = InternationalAd::orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'International Ads fetched successfully',
            'data' => $ads,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad_title'            => 'required|string|max:255',
            'type'                => 'nullable|in:domestic,international',
            'date_published'      => 'nullable|date',
            'platform'            => 'nullable|string|max:100',
            'status'              => 'nullable|string|max:50',
            'goal'                => 'nullable|string|max:100',
            'audience'            => 'nullable|array|max:100',
            'budget_set'          => 'nullable|numeric|min:0',
            'views'               => 'nullable|integer|min:0',
            'reach'               => 'nullable|integer|min:0',
            'messages_received'   => 'nullable|integer|min:0',
            'cost_per_message'    => 'nullable|numeric|min:0',
            'top_location'        => 'nullable|string|max:255',
            'post_reactions'      => 'nullable|integer|min:0',
            'post_shares'         => 'nullable|integer|min:0',
            'post_save'           => 'nullable|integer|min:0',
            'total_amount_spend'  => 'nullable|numeric|min:0',
            'duration'            => 'nullable|string|max:50',
            'post_type'            => 'nullable|string|max:100',

        ]);

        $data['date_published'] = $request->date_published 
            ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->date_published)->format('Y-m-d') 
            : null;

        if ($data['type'] === 'domestic') {
            $ad = Ad::create($data);

        } else {
            $ad = InternationalAd::create($data);
        }
    

        return response()->json([
            'message' => 'Ad created successfully',
            'data'    => $ad,
        ], 201);
    }

    public function show($id)
        {
            $ad = Ad::find($id);

            if ($ad) {
                return response()->json([
                    'success' => true,
                    'type' => 'domestic',
                    'data' => $ad,
                ], 200);
            }

            $internationalAd = InternationalAd::find($id);

            if ($internationalAd) {
                return response()->json([
                    'success' => true,
                    'type' => 'international',
                    'data' => $internationalAd,
                ], 200);
            }

            // If not found in either table
            return response()->json([
                'success' => false,
                'message' => 'Ad not found',
            ], 404);
        }

    
        public function update(Request $request, $id)
        {
            $data = $request->validate([
                'ad_title'            => 'sometimes|required|string|max:255',
                'type'                => 'sometimes|in:domestic,international',
                'date_published'      => 'nullable|date',
                'platform'            => 'nullable|string|max:100',
                'status'              => 'nullable|string|max:50',
                'goal'                => 'nullable|string|max:100',
                'audience'            => 'nullable|array|max:100',
                'budget_set'          => 'nullable|numeric|min:0',
                'views'               => 'nullable|integer|min:0',
                'reach'               => 'nullable|integer|min:0',
                'messages_received'   => 'nullable|integer|min:0',
                'cost_per_message'    => 'nullable|numeric|min:0',
                'top_location'        => 'nullable|string|max:255',
                'post_reactions'      => 'nullable|integer|min:0',
                'post_shares'         => 'nullable|integer|min:0',
                'post_save'           => 'nullable|integer|min:0',
                'total_amount_spend'  => 'nullable|numeric|min:0',
                'duration'            => 'nullable|string|max:50',
                'post_type'            => 'nullable|string|max:100',

            ]);
        
            if (($data['type'] ?? null) === 'international') {
                $model = InternationalAd::findOrFail($id);
            } else {
                $model = Ad::findOrFail($id);
            }
        
            $model->update($data);
        
            return response()->json([
                'message' => 'Ad updated successfully',
                'data'    => $model,
            ], 200);
        }
        
        public function destroy($id)
        {
            $ad = Ad::find($id);
        
            if ($ad) {
                $ad->delete();
                return response()->json([
                    'success' => true,
                    'type'    => 'domestic',
                    'message' => 'Domestic ad deleted successfully.',
                ], 200);
            }
        
            $intlAd = InternationalAd::find($id);
        
            if ($intlAd) {
                $intlAd->delete();
                return response()->json([
                    'success' => true,
                    'type'    => 'international',
                    'message' => 'International ad deleted successfully.',
                ], 200);
            }
        
            return response()->json([
                'success' => false,
                'message' => 'Ad not found.',
            ], 404);
        }
        
}
