<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Settings\Requests\StoreSettingsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService
    ) {
        $this->middleware('auth');
        $this->middleware('can:settings.view')->only(['index', 'show']);
        $this->middleware('can:settings.create')->only(['create', 'store']);
        $this->middleware('can:settings.edit')->only(['edit', 'update']);
        $this->middleware('can:settings.delete')->only(['destroy']);
    }

    /**
     * Display settings grouped by category
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['category', 'search', 'per_page']);
        $settings = $this->settingsService->getForAdmin($filters);
        $categories = $this->settingsService->getCategories();
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Show form for creating new setting
     */
    public function create(): Response
    {
        $categories = $this->settingsService->getCategories();
        
        return Inertia::render('Settings/Form', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new setting
     */
    public function store(StoreSettingsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Handle file upload
            if ($request->hasFile('file') && $data['type'] === 'file') {
                $path = $this->settingsService->uploadFile($request->file('file'), $data['key']);
                $data['value'] = $path;
            }
            
            $setting = $this->settingsService->createOrUpdate($data);
            
            return response()->json([
                'success' => true,
                'message' => __('settings.created_successfully'),
                'setting' => $setting,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.creation_failed'),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show edit form for setting
     */
    public function edit(Settings $setting): Response
    {
        $categories = $this->settingsService->getCategories();
        
        return Inertia::render('Settings/Form', [
            'setting' => $setting,
            'categories' => $categories,
        ]);
    }

    /**
     * Update setting
     */
    public function update(StoreSettingsRequest $request, Settings $setting): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Handle file upload
            if ($request->hasFile('file') && $data['type'] === 'file') {
                $path = $this->settingsService->uploadFile($request->file('file'), $data['key']);
                $data['value'] = $path;
            }
            
            $setting->update($data);
            $this->settingsService->clearAllCache();
            
            return response()->json([
                'success' => true,
                'message' => __('settings.updated_successfully'),
                'setting' => $setting->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.update_failed'),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete setting
     */
    public function destroy(Settings $setting): JsonResponse
    {
        try {
            $this->settingsService->delete($setting->key);
            
            return response()->json([
                'success' => true,
                'message' => __('settings.deleted_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.deletion_failed'),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);
        
        try {
            $success = $this->settingsService->updateMultiple($request->input('settings'));
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => __('settings.updated_successfully'),
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => __('settings.update_failed'),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.update_failed'),
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get public settings for frontend
     */
    public function getPublic(): JsonResponse
    {
        try {
            $settings = $this->settingsService->getPublicSettings();
            
            return response()->json([
                'success' => true,
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch public settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all settings cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->settingsService->clearAllCache();
            
            return response()->json([
                'success' => true,
                'message' => __('settings.cache_cleared'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.cache_clear_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}