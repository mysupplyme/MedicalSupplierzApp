<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use Illuminate\Http\Request;

class SubscriptionPackageController extends Controller
{
    public function index()
    {
        $packages = BusinessSubscription::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $packages]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:191',
            'description_en' => 'nullable|string',
            'period' => 'required|integer|min:1',
            'type' => 'required|in:month,year',
            'cost' => 'required|integer|min:0',
            'ios_plan_id' => 'nullable|string|max:191',
            'android_plan_id' => 'nullable|string|max:191',
            'status' => 'required|boolean'
        ]);

        $package = BusinessSubscription::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Subscription package created successfully',
            'data' => $package
        ]);
    }

    public function show($id)
    {
        $package = BusinessSubscription::find($id);
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $package]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:191',
            'description_en' => 'nullable|string',
            'period' => 'required|integer|min:1',
            'type' => 'required|in:month,year',
            'cost' => 'required|integer|min:0',
            'ios_plan_id' => 'nullable|string|max:191',
            'android_plan_id' => 'nullable|string|max:191',
            'status' => 'required|boolean'
        ]);

        $package = BusinessSubscription::find($id);
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found'], 404);
        }

        $package->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Subscription package updated successfully',
            'data' => $package
        ]);
    }

    public function destroy($id)
    {
        $package = BusinessSubscription::find($id);
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found'], 404);
        }

        $package->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription package deleted successfully'
        ]);
    }
}