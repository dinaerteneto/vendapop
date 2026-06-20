<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;

class IconController extends Controller
{
    public function show($storeSlug)
    {
        $tenant = Tenant::where('slug', $storeSlug)->firstOrFail();

        if ($tenant->logo_url) {
            return redirect($tenant->logo_url, 302)
                ->header('Cache-Control', 'public, max-age=604800');
        }

        $size = (int) request()->query('size', 512);
        $size = in_array($size, [16, 32, 64, 180, 192, 512]) ? $size : 512;

        $primaryColor = $tenant->primary_color ?: '#7c3aed';

        $img = imagecreatetruecolor($size, $size);

        imagealphablending($img, false);
        imagesavealpha($img, true);

        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagealphablending($img, true);

        $bgColor = $this->hexToAlloc($img, $primaryColor);
        $centerX = (int) ($size / 2);
        $centerY = (int) ($size / 2);
        $radius = (int) ($size / 2) - 1;
        imagefilledellipse($img, $centerX, $centerY, $radius * 2, $radius * 2, $bgColor);

        $name = trim($tenant->name);
        $words = explode(' ', $name);
        $initials = '';

        if (count($words) >= 2) {
            $first = mb_substr($words[0], 0, 1);
            $last = mb_substr($words[count($words) - 1], 0, 1);
            $initials = mb_strtoupper($first . $last);
        } else {
            $initials = mb_strtoupper(mb_substr($name, 0, 2));
        }

        $fontCandidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', // Debian/Ubuntu
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',           // Alpine
        ];
        $fontPath = null;
        foreach ($fontCandidates as $candidate) {
            if (file_exists($candidate)) {
                $fontPath = $candidate;
                break;
            }
        }

        if ($fontPath !== null) {
            $white = imagecolorallocate($img, 255, 255, 255);

            $fontSize = (int) ($size * 0.4);
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $initials);
            $textWidth = $bbox[2] - $bbox[0];
            $textHeight = $bbox[1] - $bbox[7];

            $x = (int) (($size - $textWidth) / 2);
            $y = (int) (($size - $textHeight) / 2) - $bbox[7];

            imagettftext($img, $fontSize, 0, $x, $y, $white, $fontPath, $initials);
        }

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return response($pngData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=604800, immutable')
            ->header('Access-Control-Allow-Origin', '*');
    }

    private function hexToAlloc($img, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($img, $r, $g, $b);
    }
}
