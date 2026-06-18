<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProxyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
        ]);

        try {
            $response = Http::timeout(10)->get($validated['url']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Não foi possível carregar a imagem. Verifique o link e tente novamente.'], 422);
        }

        if (!$response->successful()) {
            return response()->json(['message' => 'Não foi possível carregar a imagem. Verifique o link e tente novamente.'], 422);
        }

        $contentType = $response->header('Content-Type');
        if (!$contentType || !str_starts_with($contentType, 'image/')) {
            return response()->json(['message' => 'O link não aponta para uma imagem válida.'], 422);
        }

        $ext = match(true) {
            str_contains($contentType, 'jpeg') => 'jpg',
            str_contains($contentType, 'png')  => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif')  => 'gif',
            default => 'jpg',
        };

        $filename = 'proxy/' . Str::uuid() . '.' . $ext;
        Storage::disk('public')->put($filename, $response->body());

        return response()->json([
            'url'  => url(Storage::url($filename)),
            'path' => $filename,
        ]);
    }
}
