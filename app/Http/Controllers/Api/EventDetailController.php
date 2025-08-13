<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventDetailController extends Controller
{
    public function getEventDetails(Request $request, $eventId)
    {
        $event = ProductSupplier::where('product_id', $eventId)->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }
}