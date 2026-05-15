<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        return $this->successResponse('Pesan kontak berhasil diambil', ContactMessage::latest('created_at')->get());
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return $this->successResponse('Pesan kontak berhasil dihapus');
    }
}
