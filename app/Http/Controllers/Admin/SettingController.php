<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-settings');
    }

    public function edit()
    {
        $settings = Setting::allCached();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:512'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'google_analytics_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'google_search_console' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'default_og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'dark_mode_enabled' => ['boolean'],

            'adsense_publisher_id' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],

            'live_score_title' => ['nullable', 'string', 'max:100'],
            'live_score_content' => ['nullable', 'string', 'max:2000'],
            'live_score_link' => ['nullable', 'url:http,https', 'max:500'],
        ]);

        foreach (['logo', 'footer_logo', 'favicon', 'default_og_image'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $old = Setting::get($fileKey);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data[$fileKey] = $request->file($fileKey)->store('settings', 'public');
            } else {
                unset($data[$fileKey]);
            }
        }

        foreach ($data as $key => $value) {
            $group = str_starts_with($key, 'meta_') || str_contains($key, 'google') ? 'seo'
                : (in_array($key, ['primary_color', 'secondary_color', 'footer_text', 'dark_mode_enabled']) ? 'appearance'
                : ($key === 'adsense_publisher_id' ? 'ads'
                : (str_starts_with($key, 'live_score_') ? 'live_score' : 'general')));

            Setting::set($key, is_bool($value) ? (string) (int) $value : (string) $value, $group);
        }

        AuditLog::record('settings.updated', null, ['keys' => array_keys($data)]);

        return back()->with('success', 'Settings updated.');
    }
}
