<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()
            ->addresses()
            ->latest('is_default')
            ->latest()
            ->get();

        return $this->successResponse('Alamat berhasil diambil', $addresses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:25',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'full_address' => 'required|string|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create([
            ...$data,
            'province' => '-',
            'district' => '-',
            'is_default' => $request->boolean('is_default'),
        ]);

        return $this->successResponse('Alamat berhasil disimpan', $address, 201);
    }

    public function destroy(Request $request, UserAddress $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->errorResponse('Alamat tidak sesuai dengan akun pelanggan.', [], 403);
        }

        $address->delete();

        return $this->successResponse('Alamat berhasil dihapus');
    }
}
