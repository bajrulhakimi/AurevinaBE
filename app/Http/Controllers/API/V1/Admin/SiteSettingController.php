<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function show()
    {
        return $this->successResponse('Pengaturan toko berhasil diambil', SiteSettings::all());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'phone_primary' => 'nullable|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'map_url' => 'nullable|url|max:2048',
            'instagram_url' => 'nullable|url|max:2048',
            'x_url' => 'nullable|url|max:2048',
            'telegram_url' => 'nullable|url|max:2048',
            'business_hours' => 'nullable|string|max:1000',
            'about_headline' => 'nullable|string|max:500',
            'about_image_url' => 'nullable|url|max:2048',
            'about_us' => 'nullable|string|max:10000',
            'privacy_policy' => 'nullable|string|max:50000',
            'terms_conditions' => 'nullable|string|max:50000',
            'how_to_order_title' => 'nullable|string|max:255',
            'how_to_order_description' => 'nullable|string|max:1000',
            'how_to_order_steps' => 'nullable|string|max:50000',
        ]);

        return $this->successResponse('Pengaturan toko berhasil disimpan', SiteSettings::save($data));
    }
}
