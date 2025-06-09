<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function business($businessId)
    {
        // You can fetch business details from database if needed
        // $business = Business::find($businessId);
        
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
        $appDeepLink = "MaruMehsana://business/{$businessId}";
        
        return view('share.business', compact('businessId', 'playStoreUrl', 'appDeepLink'));
    }
}