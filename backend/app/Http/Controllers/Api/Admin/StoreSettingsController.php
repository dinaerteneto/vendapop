<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use App\Services\ProductAttributeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreSettingsController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        // Admin routes are guarded by Auth, but we need to access the tenant of the logged user.
    }

    public function show(Request $request)
    {
        return $request->user()->tenant->load('socials');
    }

    public function update(Request $request)
    {
        // Handle _method spoofing for POST requests with file uploads
        if ($request->method() === 'POST' && $request->has('_method')) {
            $request->setMethod($request->input('_method'));
        }

        $tenant = $request->user()->tenant;

        // Debug: log all incoming data
        Log::info('StoreSettings update request', [
            'all_input' => $request->all(),
            'name' => $request->input('name'),
            'whatsapp_number' => $request->input('whatsapp_number'),
            'has_file' => $request->hasFile('logo'),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Check if required fields are present before validation
        // This helps debug FormData issues
        $nameInput = $request->input('name');
        $whatsappInput = $request->input('whatsapp_number');

        Log::info('StoreSettings - Input values before validation', [
            'has_name' => $request->has('name'),
            'has_whatsapp' => $request->has('whatsapp_number'),
            'name_value' => $nameInput,
            'whatsapp_value' => $whatsappInput,
            'name_type' => gettype($nameInput),
            'whatsapp_type' => gettype($whatsappInput),
            'all_keys' => array_keys($request->all()),
        ]);

        if (!$request->has('name') || !$request->has('whatsapp_number')) {
            Log::warning('Missing required fields in FormData', [
                'has_name' => $request->has('name'),
                'has_whatsapp' => $request->has('whatsapp_number'),
                'all_keys' => array_keys($request->all()),
                'name_value' => $nameInput,
                'whatsapp_value' => $whatsappInput,
            ]);
        }

        // If fields are missing or empty, try to get from tenant
        if (empty($nameInput)) {
            $nameInput = $tenant->name;
        }
        if (empty($whatsappInput)) {
            $whatsappInput = $tenant->whatsapp_number;
        }

        // Merge with request data to ensure fields are present
        $request->merge([
            'name' => $nameInput,
            'whatsapp_number' => $whatsappInput,
        ]);

        // Validate required fields first, before processing optional ones
        $validated = $request->validate([
            'name' => 'required|string|min:1',
            'whatsapp_number' => 'required|string|min:1',
            'whatsapp_message' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'description' => 'nullable|string',
            'banner_message' => 'nullable|string',
            'banner_text_color_1' => 'nullable|string',
            'banner_text_color_2' => 'nullable|string',
            'banner_background_color' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'logo_url' => 'nullable|string',
            'address' => 'nullable|string',
            'email_contact' => 'nullable|string|email',
            'business_sector' => 'nullable|in:fashion,electronics,jewelry,real_estate,food,custom_orders,affiliates,other',
            'socials' => 'nullable',
        ]);

        // Handle logo upload FIRST - PRIORITY: file upload overrides URL
        // This must be done BEFORE converting empty strings to null
        if ($request->hasFile('logo')) {
            Log::info('StoreSettings: Processing logo file upload', [
                'file_name' => $request->file('logo')->getClientOriginalName(),
                'file_size' => $request->file('logo')->getSize(),
            ]);

            // Delete old logo if exists and is local
            if ($tenant->logo_path && !$tenant->logo_is_external) {
                Storage::disk('public')->delete($tenant->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $logoUrl = url(Storage::url($path));

            Log::info('StoreSettings: Logo uploaded successfully', [
                'path' => $path,
                'logo_url' => $logoUrl,
            ]);

            // Set logo_url to local URL and mark as not external
            // Override any logo_url that might have been sent in the request
            $validated['logo_url'] = $logoUrl;
            $validated['logo_path'] = $path;
            $validated['logo_is_external'] = false;
        }

        // Convert empty strings to null for nullable fields (FormData sends empty strings)
        // But skip logo_url if we just uploaded a file
        $nullableFields = ['whatsapp_message', 'primary_color', 'secondary_color', 'description',
                          'banner_message', 'banner_text_color_1', 'banner_text_color_2',
                          'banner_background_color', 'address', 'email_contact', 'business_sector'];

        foreach ($nullableFields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        // Handle logo_url only if no file was uploaded
        if (!$request->hasFile('logo')) {
            // Check if logo_url is explicitly set to null (removal request)
            $logoUrlValue = $validated['logo_url'] ?? $request->input('logo_url');

            // Handle explicit null or empty string (removal)
            if ($logoUrlValue === null || (is_string($logoUrlValue) && trim($logoUrlValue) === '')) {
                Log::info('StoreSettings: Removing logo', [
                    'current_logo_path' => $tenant->logo_path,
                    'current_logo_is_external' => $tenant->logo_is_external,
                ]);

                // Delete old logo if exists and is local
                if ($tenant->logo_path && !$tenant->logo_is_external) {
                    Storage::disk('public')->delete($tenant->logo_path);
                }
                $validated['logo_url'] = null;
                $validated['logo_path'] = null;
                $validated['logo_is_external'] = false;
            } elseif (!empty($logoUrlValue) && is_string($logoUrlValue)) {
                // URL provided - check if it's a local URL or external
                // URL provided - check if it's a local URL or external
                $logoUrl = trim($logoUrlValue);
                $storageUrl = url('/storage/');

                if (str_starts_with($logoUrl, $storageUrl)) {
                    // It's a local URL - extract path
                    $path = str_replace($storageUrl . '/', '', $logoUrl);
                    $validated['logo_url'] = $logoUrl;
                    $validated['logo_path'] = $path;
                    $validated['logo_is_external'] = false;
                } else {
                    // It's an external URL
                    $validated['logo_url'] = $logoUrl;
                    $validated['logo_path'] = null;
                    $validated['logo_is_external'] = true;

                    // If we had a local logo before, delete it
                    if ($tenant->logo_path && !$tenant->logo_is_external) {
                        Storage::disk('public')->delete($tenant->logo_path);
                    }
                }
            }
        }

        // Remove logo from validated if it's a file (already handled)
        unset($validated['logo']);

        // Check if business_sector changed
        $businessSectorChanged = isset($validated['business_sector']) && 
                                 $tenant->business_sector !== $validated['business_sector'];

        $tenant->update($validated);

        // Create default attributes if business_sector was set/changed
        if ($businessSectorChanged && $validated['business_sector']) {
            $attributeService = app(ProductAttributeService::class);
            $attributeService->createDefaultAttributesForSector($tenant, $validated['business_sector']);
        }

        // Handle socials - can come as JSON string in FormData or as array in JSON
        if ($request->has('socials')) {
            $socialsData = $request->input('socials');

            // If it's a JSON string (from FormData), decode it
            if (is_string($socialsData)) {
                $socialsData = json_decode($socialsData, true);
            }

            // Validate socials if array
            if (is_array($socialsData)) {
                foreach ($socialsData as $social) {
                    if (empty($social['name']) || empty($social['url'])) {
                        continue; // Skip invalid entries
                    }
                }

                if (count($socialsData) > 0) {
                    $tenant->socials()->delete();
                    foreach ($socialsData as $social) {
                        if (!empty($social['name']) && !empty($social['url'])) {
                            $tenant->socials()->create([
                                'name' => $social['name'],
                                'url' => $social['url'],
                                'icon' => $social['icon'] ?? null,
                            ]);
                        }
                    }
                } else {
                    // If empty array, delete all socials
                    $tenant->socials()->delete();
                }
            }
        }

        return response()->json($tenant->load('socials'));
    }
}
