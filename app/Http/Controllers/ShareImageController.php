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

        // Add White Banner at bottom
        imagefilledrectangle($canvas, 0, $height, $width, $newHeight, $white);

        // Add professional blue accent strip at the top of the banner (instead of a simple line)
        imagefilledrectangle($canvas, 0, $height, $width, $height + 4, $blue);

        // Fonts
        $fontSizeTitle = round($bannerHeight * 0.22);
        $fontSizeSub = round($bannerHeight * 0.14);
        
        // Try to find a font
        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        if (!file_exists($fontPath)) {
            $fontPath = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
        }

        if (file_exists($fontPath)) {
            // Draw "Maru Mehsana" Brand (Centered vertically in the banner area)
            imagettftext($canvas, $fontSizeTitle, 0, 30, $height + ($bannerHeight * 0.48), $blue, $fontPath, "Maru Mehsana");
            
            // Draw Play Store CTA (Subtle and professional)
            $ctaText = "Download Maru Mehsana from Play Store";
            imagettftext($canvas, $fontSizeSub, 0, 30, $height + ($bannerHeight * 0.78), $gray, $fontPath, $ctaText);
            
            // Draw "Shared via" on the right (Right aligned)
            $rightText = "Shared from Maru Mehsana " . $type;
            $bbox = imagettfbbox($fontSizeSub, 0, $fontPath, $rightText);
            $textWidth = $bbox[2] - $bbox[0];
            imagettftext($canvas, $fontSizeSub, 0, $width - $textWidth - 30, $height + ($bannerHeight * 0.6), $black, $fontPath, $rightText);
        } else {
            // Fallback to builtin fonts
            imagestring($canvas, 5, 20, $height + 25, "Maru Mehsana", $blue);
            imagestring($canvas, 3, 20, $height + 55, "Download from Play Store", $gray);
        }

        // Output image to buffer
        ob_start();
        imagejpeg($canvas, null, 90);
        $imageData = ob_get_clean();

        // Cleanup
        imagedestroy($source);
        imagedestroy($canvas);

        return response($imageData)->header('Content-Type', 'image/jpeg');
    }
}
