<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        $message = ContactMessage::create($request->validated());

        return $this->successResponse('Pesan berhasil dikirim', $message, 201);
    }
}
