<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Support\SiteSettings;

class SiteSettingController extends Controller
{
    public function show()
    {
        return $this->successResponse('Pengaturan toko berhasil diambil', SiteSettings::all());
    }
}
