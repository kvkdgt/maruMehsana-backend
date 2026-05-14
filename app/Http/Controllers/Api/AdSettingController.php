<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdSettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\AdSetting::all();
        $formatted = [];
        
        foreach ($settings as $setting) {
            $formatted[$setting->placement_key] = [
                'is_active' => (bool)$setting->is_active,
                'ad_unit_id' => $setting->ad_unit_id_android
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }
}
