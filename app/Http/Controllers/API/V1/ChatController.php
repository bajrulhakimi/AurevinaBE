<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(Request $request)
    {
        $thread = $this->threadFor($request);
        $this->markAdminMessagesAsRead($thread, $request->user()->id);

        return $this->successResponse('Percakapan berhasil diambil', $this->formatThread($thread->fresh(['customer', 'messages.sender', 'messages.product'])));
    }

    public function unreadCount(Request $request)
    {
        $thread = ChatThread::where('customer_id', $request->user()->id)->first();

        if (!$thread) {
            return $this->successResponse('Jumlah pesan belum dibaca berhasil diambil', ['unread_count' => 0]);
        }

        $count = $thread->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $request->user()->id)
            ->whereHas('sender', fn($query) => $query->where('role', 'admin'))
            ->count();

        return $this->successResponse('Jumlah pesan belum dibaca berhasil diambil', ['unread_count' => $count]);
    }

    public function storeMessage(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'product_id' => 'nullable|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $thread = $this->threadFor($request);
        if (!empty($data['order_id'])) {
            $belongsToCustomer = $request->user()->orders()->where('id', $data['order_id'])->exists();
            if (!$belongsToCustomer) {
                return $this->errorResponse('Pesanan tidak sesuai dengan akun pelanggan.', [], 422);
            }
        }

        $thread->messages()->create([
            'sender_id' => $request->user()->id,
            'product_id' => $data['product_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'message' => $data['message'],
        ]);
        $thread->touch();

        return $this->successResponse('Pesan berhasil dikirim', $this->formatThread($thread->fresh(['customer', 'messages.sender', 'messages.product', 'messages.order'])), 201);
    }

    private function threadFor(Request $request): ChatThread
    {
        return ChatThread::firstOrCreate(
            ['customer_id' => $request->user()->id],
            ['status' => 'open']
        )->load(['customer', 'messages.sender', 'messages.product', 'messages.order']);
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

    private function markAdminMessagesAsRead(ChatThread $thread, int $customerId): void
    {
        $thread->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $customerId)
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
