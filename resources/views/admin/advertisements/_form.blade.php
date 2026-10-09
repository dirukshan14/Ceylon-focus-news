@csrf
@if($ad ?? false) @method('PUT') @endif

<div class="max-w-2xl bg-white rounded-lg border p-4 space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">Ad Name</label>
        <input type="text" name="name" required value="{{ old('name', $ad->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Type</label>
        <select name="type" id="ad-type" class="w-full border rounded px-3 py-2 text-sm" onchange="toggleAdFields()">
            <option value="image" @selected(old('type', $ad->type ?? '') === 'image')>Image / Banner</option>
            <option value="html" @selected(old('type', $ad->type ?? '') === 'html')>HTML / JavaScript Code</option>
            <option value="adsense" @selected(old('type', $ad->type ?? '') === 'adsense')>Google AdSense Code</option>
        </select>
    </div>

    <div id="field-image">
        <label class="block text-sm font-medium mb-1">Banner Image</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
        @if(isset($ad) && $ad->image)
            <img src="{{ asset('storage/'.$ad->image) }}" class="mt-2 h-16 rounded">
        @endif
        <label class="block text-sm font-medium mt-3 mb-1">Click-through URL</label>
        <input type="url" name="click_url" value="{{ old('click_url', $ad->click_url ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>

    <div id="field-html">
        <label class="block text-sm font-medium mb-1">HTML / JS / AdSense Code</label>
        <textarea name="html_code" rows="5" class="w-full border rounded px-3 py-2 text-sm font-mono text-xs">{{ old('html_code', $ad->html_code ?? '') }}</textarea>
        <p class="text-xs text-gray-400 mt-1">Rendered inside a sandboxed slot on the public site. Only paste code from trusted ad networks.</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Position</label>
        <select name="position" required class="w-full border rounded px-3 py-2 text-sm">
            @foreach(['header','top_banner','homepage_top','homepage_middle','homepage_bottom','sidebar','article_top','article_middle','article_bottom','between_cards','video_page','footer'] as $pos)
                <option value="{{ $pos }}" @selected(old('position', $ad->position ?? '') === $pos)>{{ ucwords(str_replace('_',' ',$pos)) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Device Targeting</label>
        <select name="device_target" class="w-full border rounded px-3 py-2 text-sm">
            @foreach(['all','desktop','tablet','mobile'] as $device)
                <option value="{{ $device }}" @selected(old('device_target', $ad->device_target ?? 'all') === $device)>{{ ucfirst($device) }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <input type="datetime-local" name="starts_at"
                   value="{{ old('starts_at', isset($ad) && $ad->starts_at ? $ad->starts_at->format('Y-m-d\TH:i') : '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">End Date</label>
            <input type="datetime-local" name="ends_at"
                   value="{{ old('ends_at', isset($ad) && $ad->ends_at ? $ad->ends_at->format('Y-m-d\TH:i') : '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Priority (higher shows first)</label>
        <input type="number" name="priority" value="{{ old('priority', $ad->priority ?? 0) }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $ad->is_active ?? true))> Active
    </label>

    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
        {{ isset($ad) ? 'Update Advertisement' : 'Create Advertisement' }}
    </button>
</div>

<script>
    function toggleAdFields() {
        const type = document.getElementById('ad-type').value;
        document.getElementById('field-image').style.display = type === 'image' ? 'block' : 'none';
        document.getElementById('field-html').style.display = type !== 'image' ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', toggleAdFields);
</script>
