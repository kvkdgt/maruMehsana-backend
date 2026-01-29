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
        // Fixed professional height for the branded footer
        $bannerHeight = max(100, round($width * 0.15));
        if ($bannerHeight > 180) $bannerHeight = 180;
        
        $newHeight = $height + $bannerHeight;

        // Create new true color image
        $canvas = imagecreatetruecolor($width, $newHeight);

        // Colors from Reference
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $brandBlue = imagecolorallocate($canvas, 1, 105, 178); // #0169b2 - Primary Brand Blue
        $darkBlue = imagecolorallocate($canvas, 1, 85, 145);  // Slightly darker for depth
        $lightBlue = imagecolorallocate($canvas, 200, 225, 245);

        // Fill background
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        // Draw the Blue Banner (Footer)
        imagefilledrectangle($canvas, 0, $height, $width, $newHeight, $brandBlue);

        // Layout Constants
        $padding = $bannerHeight * 0.2;
        $vCenter = $height + ($bannerHeight / 2);
        
        // Font paths
        $fontBold = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
        $fontReg = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        if (!file_exists($fontBold)) {
            $fontBold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
            $fontReg = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }

        if (file_exists($fontBold)) {
            // 1. Draw Map Pin Icon (left side)
            $iconSize = $bannerHeight * 0.5;
            $iconX = $padding + ($iconSize / 2);
            $iconY = $vCenter;

            // Simple Map Pin shape with white fill
            imagefilledellipse($canvas, $iconX, $iconY - ($iconSize * 0.1), $iconSize, $iconSize, $white);
            
            // The "M" inside the pin
            $mSize = $iconSize * 0.5;
            $bboxM = imagettfbbox($mSize, 0, $fontBold, "M");
            $mWidth = $bboxM[2] - $bboxM[0];
            $mHeight = $bboxM[1] - $bboxM[7];
            imagettftext($canvas, $mSize, 0, $iconX - ($mWidth/2), $iconY + ($mHeight/3) - ($iconSize * 0.1), $brandBlue, $fontBold, "M");

            // 2. Draw Brand Text & Tagline
            $textX = $iconX + ($iconSize * 0.8);
            $fontSizeMain = $bannerHeight * 0.22;
            $fontSizeSub = $bannerHeight * 0.13;

            imagettftext($canvas, $fontSizeMain, 0, $textX, $vCenter - 5, $white, $fontBold, "MARU MEHSANA");
            imagettftext($canvas, $fontSizeSub, 0, $textX, $vCenter + ($fontSizeMain), $white, $fontReg, "Your City in Your Pocket");

            // 3. Vertical Separator Line
            $separatorX = $width * 0.7; // Positioned towards the right
            imageline($canvas, $separatorX, $height + ($padding * 1.5), $separatorX, $newHeight - ($padding * 1.5), $white);

            // 4. Download on Play Store Section (Right side)
            $playStoreX = $separatorX + ($padding * 0.8);
            
            // Draw a rounded rectangle for the badge-like feel
            $badgeWidth = $width - $playStoreX - $padding;
            if ($badgeWidth > $bannerHeight * 1.5) $badgeWidth = $bannerHeight * 1.5;
            
            $badgeHeight = $bannerHeight * 0.45;
            $badgeY = $vCenter - ($badgeHeight / 2);
            
            // Draw Badge Border
            imagerectangle($canvas, $playStoreX, $badgeY, $playStoreX + $badgeWidth, $badgeY + $badgeHeight, $white);
            
            // Badge Text
            $fsPlay1 = $badgeHeight * 0.25;
            $fsPlay2 = $badgeHeight * 0.4;
            imagettftext($canvas, $fsPlay1, 0, $playStoreX + ($badgeWidth * 0.3), $badgeY + ($badgeHeight * 0.4), $white, $fontReg, "Download on");
            imagettftext($canvas, $fsPlay2, 0, $playStoreX + ($badgeWidth * 0.3), $badgeY + ($badgeHeight * 0.85), $white, $fontBold, "Play Store");
            
            // Simple triangle as play logo
            $point1 = ['x' => $playStoreX + ($badgeWidth * 0.1), 'y' => $badgeY + ($badgeHeight * 0.25)];
            $point2 = ['x' => $playStoreX + ($badgeWidth * 0.1), 'y' => $badgeY + ($badgeHeight * 0.75)];
            $point3 = ['x' => $playStoreX + ($badgeWidth * 0.25), 'y' => $badgeY + ($badgeHeight * 0.5)];
            imagefilledpolygon($canvas, [$point1['x'], $point1['y'], $point2['x'], $point2['y'], $point3['x'], $point3['y']], 3, $white);

        } else {
            // High-quality fallback if fonts are missing
            imagestring($canvas, 5, 20, $height + 25, "MARU MEHSANA", $white);
            imagestring($canvas, 3, 20, $height + 55, "Your City in Your Pocket", $white);
        }

        // Output image to buffer at max quality
        ob_start();
        imagejpeg($canvas, null, 100);
        $imageData = ob_get_clean();

        // Cleanup
        imagedestroy($source);
        imagedestroy($canvas);

        return response($imageData)->header('Content-Type', 'image/jpeg');
    }
}
