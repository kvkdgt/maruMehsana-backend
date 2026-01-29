<?php

namespace App\Http\Controllers;

use App\Models\TouristPlace;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ShareImageController extends Controller
{
    public function sharePlace($id)
    {
        $place = TouristPlace::findOrFail($id);
        $imagePath = storage_path('app/public/' . $place->thumbnail);
        
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found at ' . $imagePath], 404);
        }

        return $this->generateShareImage($imagePath, $place->name, "Places");
    }

    public function shareNews($id)
    {
        $news = NewsArticle::findOrFail($id);
        // Assuming news images are in public/news or similar, but following news article model
        $imagePath = storage_path('app/public/news/' . $news->image);
        
        if (!file_exists($imagePath)) {
            // Fallback if not in news subfolder
            $imagePath = storage_path('app/public/' . $news->image);
            if (!file_exists($imagePath)) {
                return response()->json(['error' => 'News image not found'], 404);
            }
        }

        return $this->generateShareImage($imagePath, $news->title, "News");
    }

    private function generateShareImage($sourcePath, $title, $type)
    {
        // Load original image
        $info = getimagesize($sourcePath);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($sourcePath);
                break;
            default:
                return response()->file($sourcePath);
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // Banner height (15% of total height or min 100px)
        $bannerHeight = max(100, round($height * 0.15));
        $newHeight = $height + $bannerHeight;

        // Create new true color image
        $canvas = imagecreatetruecolor($width, $newHeight);

        // Colors
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 33, 33, 33);
        $blue = imagecolorallocate($canvas, 1, 105, 178); // #0169b2
        $gray = imagecolorallocate($canvas, 128, 128, 128);

        // Fill background with white
        imagefill($canvas, 0, 0, $white);

        // Copy original image
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        // Professional hybrid height banner (capped for high-res images)
        $bannerHeight = max(75, min(130, round($height * 0.12))); 
        $newHeight = $height + $bannerHeight;

        // Create new true color image
        $canvas = imagecreatetruecolor($width, $newHeight);

        // Modern Slate/Blue Palette
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $blue = imagecolorallocate($canvas, 0, 119, 182); // Premium Blue
        $slate = imagecolorallocate($canvas, 71, 85, 105); // Slate-600
        $dark = imagecolorallocate($canvas, 15, 23, 42); // Slate-900

        // Fill background with white
        imagefill($canvas, 0, 0, $white);

        // Copy original image
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        // Add 2px sleek accent strip
        imagefilledrectangle($canvas, 0, $height, $width, $height + 2, $blue);

        // Proportional Font Sizing
        $fontSizeBrand = max(14, round($width * 0.025));
        $fontSizeSub = max(9, round($fontSizeBrand * 0.55));
        
        $fontBold = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
        $fontReg = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        
        if (!file_exists($fontBold)) {
            $fontBold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
            $fontReg = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }

        if (file_exists($fontBold) && file_exists($fontReg)) {
            $padding = max(20, round($width * 0.04));
            $vCenter = $height + ($bannerHeight * 0.45);

            // Left Side: Brand Identity
            imagettftext($canvas, $fontSizeBrand, 0, $padding, $vCenter, $blue, $fontBold, "MARU MEHSANA");
            imagettftext($canvas, $fontSizeSub, 0, $padding, $vCenter + ($fontSizeBrand * 1.2), $slate, $fontReg, "Mehsana's Digital City Guide");

            // Right Side: Platform CTA
            $cta = "Get it on Google Play Store";
            $bbox = imagettfbbox($fontSizeSub + 1, 0, $fontBold, $cta);
            $ctaWidth = $bbox[2] - $bbox[0];
            imagettftext($canvas, $fontSizeSub + 1, 0, $width - $ctaWidth - $padding, $vCenter, $dark, $fontBold, $cta);
            
            $tag = "Shared via " . $type . " App";
            $bbox2 = imagettfbbox($fontSizeSub, 0, $fontReg, $tag);
            $tagWidth = $bbox2[2] - $bbox2[0];
            imagettftext($canvas, $fontSizeSub, 0, $width - $tagWidth - $padding, $vCenter + ($fontSizeBrand * 1.2), $slate, $fontReg, $tag);
            
        } else {
            // High-quality fallback
            imagestring($canvas, 5, 20, $height + 20, "MARU MEHSANA", $blue);
            imagestring($canvas, 2, 20, $height + 45, "Download from Play Store", $slate);
        }

        // Output image to buffer at 100% Quality
        ob_start();
        imagejpeg($canvas, null, 100);
        $imageData = ob_get_clean();

        // Cleanup
        imagedestroy($source);
        imagedestroy($canvas);

        return response($imageData)->header('Content-Type', 'image/jpeg');
    }
}
