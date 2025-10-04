<?php

namespace App\Http\Controllers;

use App\Models\StockNotification;

class StockNotificationController extends Controller
{
    // ✅ Get all active notifications
    public function index()
    {
        return StockNotification::where('read', false)->get();
    }

    // ✅ Mark notification as read
    public function markAsRead(StockNotification $notification)
    {
        $notification->update(['read' => true]);
        return response()->json(['message' => 'Notification marked as read']);
    }
}
