@extends('layouts.admin')
@section('title', 'Website Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
    @csrf @method('PUT')

    <div class="bg-white rounded-lg border p-4">
        <h3 class="font-semibold mb-3">General</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Website Name</label>
                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contact Email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" name="contact_address" value="{{ old('contact_address', $settings['contact_address'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Site Description</label>
                <textarea name="site_description" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Header Logo</label>
                <div class="flex items-center gap-3 mb-2 bg-slate-50 border rounded p-2">
                    <img src="{{ isset($settings['logo']) ? asset('storage/'.$settings['logo']) : asset('images/logo-header.png') }}"
                         class="h-10 w-auto object-contain" alt="Current header logo">
                    <span class="text-xs text-gray-400">{{ isset($settings['logo']) ? 'Current uploaded logo' : 'Using default bundled logo' }}</span>
                </div>
                <input type="file" name="logo" accept="image/*" class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">Shown in the site header and admin sidebar. PNG with a transparent background works best.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Footer Logo</label>
                <div class="flex items-center gap-3 mb-2 bg-slate-800 border rounded p-2">
                    <img src="{{ isset($settings['footer_logo']) ? asset('storage/'.$settings['footer_logo']) : asset('images/logo-footer.jpg') }}"
                         class="h-10 w-auto object-contain" alt="Current footer logo">
                    <span class="text-xs text-slate-300">{{ isset($settings['footer_logo']) ? 'Current uploaded logo' : 'Using default bundled logo' }}</span>
                </div>
                <input type="file" name="footer_logo" accept="image/*" class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">Shown at the bottom of every page. Can be different from the header logo (e.g. a version made for a dark background).</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Favicon</label>
                <input type="file" name="favicon" accept="image/*" class="w-full text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-4">
        <h3 class="font-semibold mb-3">SEO</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Search Console Verification</label>
                <input type="text" name="google_search_console" value="{{ old('google_search_console', $settings['google_search_console'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-4">
        <h3 class="font-semibold mb-3">Appearance</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Primary Color</label>
                <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#1e3a8a') }}" class="w-full h-10 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Secondary Color</label>
                <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings['secondary_color'] ?? '#dc2626') }}" class="w-full h-10 border rounded">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Footer Text</label>
                <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-4">
        <h3 class="font-semibold mb-3">Advertisement</h3>
        <label class="block text-sm font-medium mb-1">AdSense Publisher ID</label>
        <input type="text" name="adsense_publisher_id" value="{{ old('adsense_publisher_id', $settings['adsense_publisher_id'] ?? '') }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="ca-pub-xxxxxxxxxxxxxxxx">
    </div>

    <div class="bg-white rounded-lg border p-4">
        <h3 class="font-semibold mb-3">Live Score</h3>
        <p class="text-xs text-gray-500 mb-3">Shown as a sidebar widget on the homepage and linked from the main navigation. There's no live sports data feed connected - update this text manually whenever scores change.</p>
        <div>
            <label class="block text-sm font-medium mb-1">Widget Title</label>
            <input type="text" name="live_score_title" value="{{ old('live_score_title', $settings['live_score_title'] ?? 'நேரடி ஸ்கோர்') }}" class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div class="mt-3">
            <label class="block text-sm font-medium mb-1">Score / Match Details</label>
            <textarea name="live_score_content" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="e.g. இலங்கை 245/6 (42 ஓவர்) vs இந்தியா">{{ old('live_score_content', $settings['live_score_content'] ?? '') }}</textarea>
        </div>
        <div class="mt-3">
            <label class="block text-sm font-medium mb-1">Full Scorecard Link (optional)</label>
            <input type="url" name="live_score_link" value="{{ old('live_score_link', $settings['live_score_link'] ?? '') }}" placeholder="https://..." class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <button type="submit" class="bg-brand text-white rounded px-6 py-2 text-sm font-medium">Save Settings</button>
</form>
@endsection
