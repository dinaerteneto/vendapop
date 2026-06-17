<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $entries = WaitlistEntry::orderBy('created_at', 'desc')->get();

        return response()->json($entries);
    }

    public function count(): JsonResponse
    {
        return response()->json(['total' => WaitlistEntry::count()]);
    }
}
