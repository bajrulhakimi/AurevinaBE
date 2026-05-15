<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use App\Models\Order;

class NotificationController extends Controller
{
    public function index()
    {
        $newOrders = Order::query()
            ->whereIn('order_status', ['pending', 'confirmed'])
            ->count();

        $unreadChats = ChatThread::query()
            ->whereHas('messages', function ($query) {
                $query
                    ->whereNull('read_at')
                    ->whereHas('sender', fn($senderQuery) => $senderQuery->where('role', 'customer'));
            })
            ->count();

        $latestOrders = Order::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'order_code', 'order_status', 'total_price', 'created_at']);

        $latestChats = ChatThread::query()
            ->with(['customer:id,name,email', 'latestMessage.sender'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($thread) => [
                'id' => $thread->id,
                'customer' => $thread->customer,
                'message' => $thread->latestMessage?->message,
                'sender_role' => $thread->latestMessage?->sender?->role,
                'updated_at' => $thread->updated_at,
            ]);

        return $this->successResponse('Notifikasi admin berhasil diambil', [
            'new_orders' => $newOrders,
            'unread_chats' => $unreadChats,
            'total' => $newOrders + $unreadChats,
            'latest_orders' => $latestOrders,
            'latest_chats' => $latestChats,
        ]);
    }
}
