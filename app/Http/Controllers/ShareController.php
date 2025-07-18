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
        $appDeepLink = "marumehsana://business/{$businessId}";
        
        return view('share.business', compact('businessId', 'playStoreUrl', 'appDeepLink'));
    }

     public function place($placeId)
    {
        // You can fetch business details from database if needed
        // $business = Business::find($businessId);
        
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
        $appDeepLink = "marumehsana://place/{$placeId}";
        
        return view('share.place', compact('placeId', 'playStoreUrl', 'appDeepLink'));
    }
         public function news($newsId)
    {
        // You can fetch business details from database if needed
        // $business = Business::find($businessId);
        
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.MaruMehsana';
        $appDeepLink = "marumehsana://news/{$newsId}";
        
        return view('share.news', compact('newsId', 'playStoreUrl', 'appDeepLink'));
    }

}