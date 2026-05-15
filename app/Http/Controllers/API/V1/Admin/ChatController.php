<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $threads = ChatThread::with(['customer', 'latestMessage.sender', 'latestMessage.product', 'latestMessage.order'])
            ->latest('updated_at')
            ->get()
            ->map(fn($thread) => $this->formatSummary($thread));

        return $this->successResponse('Daftar chat berhasil diambil', $threads);
    }

    public function show(ChatThread $chat)
    {
        $this->markCustomerMessagesAsRead($chat);

        return $this->successResponse('Detail chat berhasil diambil', $this->formatThread($chat->load(['customer', 'messages.sender', 'messages.product', 'messages.order'])));
    }

    public function storeMessage(Request $request, ChatThread $chat)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'product_id' => 'nullable|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $chat->messages()->create([
            'sender_id' => $request->user()->id,
            'product_id' => $data['product_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'message' => $data['message'],
        ]);
        $chat->touch();

        return $this->successResponse('Balasan chat berhasil dikirim', $this->formatThread($chat->fresh(['customer', 'messages.sender', 'messages.product', 'messages.order'])), 201);
    }

    public function startMessage(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'nullable|exists:products,id',
            'message' => 'required|string|max:2000',
        ]);

        $order = Order::with('items')->findOrFail($data['order_id']);

        if (!empty($data['product_id'])) {
            $belongsToOrder = $order->items->contains('product_id', (int) $data['product_id']);
            if (!$belongsToOrder) {
                return $this->errorResponse('Produk tidak termasuk dalam pesanan ini.', [], 422);
            }
        }

        $thread = ChatThread::firstOrCreate(
            ['customer_id' => $order->user_id],
            ['status' => 'open']
        );

        $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'order_id' => $order->id,
            'product_id' => $data['product_id'] ?? null,
            'message' => $data['message'],
        ]);
        $thread->touch();

        return $this->successResponse('Chat pembeli berhasil dibuat', $this->formatThread($thread->fresh(['customer', 'messages.sender', 'messages.product', 'messages.order'])), 201);
    }

    private function formatSummary(ChatThread $thread): array
    {
        return [
            'id' => $thread->id,
            'status' => $thread->status,
            'updated_at' => $thread->updated_at,
            'customer' => $thread->customer,
            'latest_message' => $thread->latestMessage ? [
                'message' => $thread->latestMessage->message,
                'created_at' => $thread->latestMessage->created_at,
                'sender' => $thread->latestMessage->sender,
                'product' => $this->formatProduct($thread->latestMessage->product),
                'order' => $this->formatOrder($thread->latestMessage->order),
            ] : null,
            'unread_count' => $thread->messages()
                ->whereNull('read_at')
                ->whereHas('sender', fn($query) => $query->where('role', 'customer'))
                ->count(),
        ];
    }

    private function formatThread(ChatThread $thread): array
    {
        return [
            'id' => $thread->id,
            'status' => $thread->status,
            'customer' => $thread->customer,
            'messages' => $thread->messages->map(fn($message) => [
                'id' => $message->id,
                'message' => $message->message,
                'created_at' => $message->created_at,
                'sender' => $message->sender,
                'product' => $this->formatProduct($message->product),
                'order' => $this->formatOrder($message->order),
            ]),
        ];
    }

    private function markCustomerMessagesAsRead(ChatThread $thread): void
    {
        $thread->messages()
            ->whereNull('read_at')
            ->whereHas('sender', fn($query) => $query->where('role', 'customer'))
            ->update(['read_at' => now()]);
    }

    private function formatProduct($product): ?array
    {
        if (!$product) {
            return null;
        }

        $image = $product->main_image
            ? (Str::startsWith($product->main_image, ['http://', 'https://']) ? $product->main_image : asset('storage/' . $product->main_image))
            : null;

        return [
            'id' => $product->id,
            'product_name' => $product->product_name,
            'base_price' => (float) $product->base_price,
            'main_image' => $image,
        ];
    }

    private function formatOrder($order): ?array
    {
        if (!$order) {
            return null;
        }

        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'order_status' => $order->order_status,
            'total_price' => (float) $order->total_price,
        ];
    }
}
