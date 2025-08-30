<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientSubscription;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Client::where('clients.buyer_type', 'doctor')
            ->leftJoin('categories', 'clients.specialty_id', '=', 'categories.id')
            ->select('clients.*', 'categories.title_en as specialty_name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $doctors->items(),
            'pagination' => [
                'current_page' => $doctors->currentPage(),
                'total_pages' => $doctors->lastPage(),
                'total_count' => $doctors->total()
            ]
        ]);
    }

    public function show($id)
    {
        $doctor = Client::where('id', $id)
            ->where('buyer_type', 'doctor')
            ->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $doctor]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:0,1']);

        $doctor = Client::where('id', $id)->where('buyer_type', 'doctor')->first();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor not found'], 404);
        }

        $doctor->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor status updated successfully'
        ]);
    }

    public function delete($id)
    {
        $doctor = Client::where('id', $id)->where('buyer_type', 'doctor')->first();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor not found'], 404);
        }

        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doctor deleted successfully'
        ]);
    }
}