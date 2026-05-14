<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdSettingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\AdSetting::all();
        return view('admin.ad_settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->input('settings', []);

        foreach ($data as $id => $values) {
            $setting = \App\Models\AdSetting::find($id);
            if ($setting) {
                $setting->update([
                    'is_active' => isset($values['is_active']),
                    'ad_unit_id_android' => $values['ad_unit_id_android'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.ad-settings')->with('success', 'Ad Settings updated successfully!');
    }
}
