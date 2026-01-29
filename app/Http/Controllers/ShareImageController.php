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
        // Define output path
        $fileName = 'share_' . md5($sourcePath . time()) . '.jpg';
        $outputPath = storage_path('app/public/share_images/' . $fileName);
        
        // Ensure directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Try Node.js HTML Rendering first (High Quality) - Only if enabled
        try {
            // Check if exec is enabled and node exists (Standard Shared Hosting usually disables this)
            $nodeAvailable = function_exists('exec') && 
                             !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions'))));

            if ($nodeAvailable) {
                $scriptPath = base_path('image-generator/render.js');
                // Escape arguments
                $cmd = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($sourcePath) . " " . escapeshellarg("MARU MEHSANA") . " " . escapeshellarg($outputPath) . " 2>&1";
                
                $output = [];
                $returnVar = 0;
                exec($cmd, $output, $returnVar);

                if ($returnVar === 0 && file_exists($outputPath)) {
                    return response()->file($outputPath);
                }
                
                // Optional: Log only if we expected it to work but it failed
                // \Illuminate\Support\Facades\Log::warning("Node Render Failed: " . implode("\n", $output));
            }
        } catch (\Throwable $e) {
             // Silent fail to fallback
        }

        // Fallback to GD (PHP Canvas) if Node fails
        
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

        // Fixed professional height - more room for the "pill" design
        $bannerHeight = max(140, round($width * 0.18));
        if ($bannerHeight > 220) $bannerHeight = 220;
        
        $newHeight = $height + $bannerHeight;

        // Create new true color image
        $canvas = imagecreatetruecolor($width, $newHeight);

        // Colors from Reference Image
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $darkBlue = imagecolorallocate($canvas, 28, 76, 138); // Exact blue from image #1c4c8a
        $bgGrey = imagecolorallocate($canvas, 245, 247, 250);

        // Fill background
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        // Draw the Blue Footer Background
        imagefilledrectangle($canvas, 0, $height, $width, $newHeight, $darkBlue);

        // Layout Constants
        $margin = $bannerHeight * 0.15;
        $vCenter = $height + ($bannerHeight / 2);
        
        // Font paths
        $fontBold = '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf';
        $fontReg = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        if (!file_exists($fontBold)) {
            $fontBold = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
            $fontReg = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        }

        if (file_exists($fontBold)) {
            // 1. Draw Map Pin Icon
            $iconHeight = $bannerHeight * 0.6;
            $iconWidth = $iconHeight * 0.8;
            $iconX = $margin * 2;
            $iconY = $vCenter;

            // Draw Pin Head (Circle)
            $headSize = $iconWidth;
            imagefilledellipse($canvas, $iconX + ($headSize/2), $iconY - ($iconHeight * 0.1), $headSize, $headSize, $white);
            
            // Draw Pin Point (Triangle/Point at bottom)
            $pointWidth = $headSize * 0.4;
            $points = [
                $iconX + ($headSize/2) - ($pointWidth/2), $iconY + ($headSize/3),
                $iconX + ($headSize/2) + ($pointWidth/2), $iconY + ($headSize/3),
                $iconX + ($headSize/2), $iconY + ($iconHeight/2)
            ];
            imagefilledpolygon($canvas, $points, 3, $white);

            // Draw "M" centered in circle
            $mSize = $headSize * 0.45;
            $bboxM = imagettfbbox($mSize, 0, $fontBold, "M");
            $mWidth = $bboxM[2] - $bboxM[0];
            $mHeight = abs($bboxM[7] - $bboxM[1]);
            imagettftext($canvas, $mSize, 0, $iconX + ($headSize/2) - ($mWidth/2), $iconY - ($iconHeight * 0.1) + ($mHeight/2), $darkBlue, $fontBold, "M");

            // 2. Text Section
            $textX = $iconX + $headSize + $margin;
            $fsMain = $bannerHeight * 0.20;
            $fsTag = $bannerHeight * 0.12;

            imagettftext($canvas, $fsMain, 0, $textX, $vCenter - ($bannerHeight * 0.05), $white, $fontBold, "MARU MEHSANA");
            imagettftext($canvas, $fsTag, 0, $textX, $vCenter + ($bannerHeight * 0.15), $white, $fontReg, "Your City in Your Pocket");

            // 3. Vertical Separator
            $sepX = $width * 0.68;
            imagesetthickness($canvas, 2);
            imageline($canvas, $sepX, $height + ($bannerHeight * 0.2), $sepX, $newHeight - ($bannerHeight * 0.2), $white);
            imagesetthickness($canvas, 1);

            // 4. Play Store Badge
            $badgeX = $sepX + $margin;
            $badgeH = $bannerHeight * 0.5;
            $badgeW = $width - $badgeX - ($margin * 2);
            if ($badgeW > $badgeH * 2.8) $badgeW = $badgeH * 2.8;
            $badgeY = $vCenter - ($badgeH / 2);

            // Draw Rounded Rectangle for Badge (using thickness for border)
            imagesetthickness($canvas, 2);
            $radius = 12;
            $x1 = $badgeX; $y1 = $badgeY; $x2 = $badgeX + $badgeW; $y2 = $badgeY + $badgeH;
            
            // Draw lines for rounded rectangle
            imageline($canvas, $x1+$radius, $y1, $x2-$radius, $y1, $white);
            imageline($canvas, $x1+$radius, $y2, $x2-$radius, $y2, $white);
            imageline($canvas, $x1, $y1+$radius, $x1, $y2-$radius, $white);
            imageline($canvas, $x2, $y1+$radius, $x2, $y2-$radius, $white);
            // Draw arcs for corners
            imagearc($canvas, $x1+$radius, $y1+$radius, $radius*2, $radius*2, 180, 270, $white);
            imagearc($canvas, $x2-$radius, $y1+$radius, $radius*2, $radius*2, 270, 360, $white);
            imagearc($canvas, $x1+$radius, $y2-$radius, $radius*2, $radius*2, 90, 180, $white);
            imagearc($canvas, $x2-$radius, $y2-$radius, $radius*2, $radius*2, 0, 90, $white);
            imagesetthickness($canvas, 1);

            // Play Logo (Triangle)
            $pSize = $badgeH * 0.4;
            $pX = $badgeX + ($badgeH * 0.25);
            $pY = $vCenter;
            $pPoints = [
                $pX, $pY - ($pSize/2),
                $pX, $pY + ($pSize/2),
                $pX + ($pSize * 0.8), $pY
            ];
            imagefilledpolygon($canvas, $pPoints, 3, $white);

            // Badge Text
            $fsDownload = $badgeH * 0.18;
            $fsStore = $badgeH * 0.32;
            $textStartX = $pX + ($pSize) + 5;
            imagettftext($canvas, $fsDownload, 0, $textStartX, $badgeY + ($badgeH * 0.42), $white, $fontReg, "Download on");
            imagettftext($canvas, $fsStore, 0, $textStartX, $badgeY + ($badgeH * 0.82), $white, $fontBold, "Play Store");

        } else {
            // High-quality fallback
            imagestring($canvas, 5, 20, $height + 30, "MARU MEHSANA", $white);
            imagestring($canvas, 3, 20, $height + 60, "Your City in Your Pocket", $white);
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
