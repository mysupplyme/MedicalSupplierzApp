<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\BusinessSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = ClientSubscription::with(['client', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscriptions->items(),
            'pagination' => [
                'current_page' => $subscriptions->currentPage(),
                'total_pages' => $subscriptions->lastPage(),
                'total_count' => $subscriptions->total()
            ]
        ]);
    }

    public function show($id)
    {
        $subscription = ClientSubscription::with(['client', 'subscription'])
            ->find($id);

        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $subscription]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,cancelled,expired']);

        $subscription = ClientSubscription::find($id);
        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $subscription->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription status updated successfully'
        ]);
    }

    public function extend(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $subscription = ClientSubscription::find($id);
        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $newEndDate = now()->parse($subscription->end_at)->addDays($request->days)->toDateString();
        $subscription->update(['end_at' => $newEndDate]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription extended successfully',
            'new_end_date' => $newEndDate
        ]);
    }

    public function stats()
    {
        $stats = [
            'total_subscriptions' => ClientSubscription::count(),
            'active_subscriptions' => ClientSubscription::where('status', 'active')->count(),
            'cancelled_subscriptions' => ClientSubscription::where('status', 'cancelled')->count(),
            'expired_subscriptions' => ClientSubscription::where('status', 'expired')->count(),
            'revenue_this_month' => ClientSubscription::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->join('bussiness_subscriptions', 'client_subscriptions.subscription_id', '=', 'bussiness_subscriptions.id')
                ->sum('bussiness_subscriptions.cost')
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}