@csrf
@if($notice ?? false) @method('PUT') @endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold text-sm mb-3">Person Details</h3>

            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text" name="name" required value="{{ old('name', $notice->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">

            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Age</label>
                    <input type="number" name="age" min="0" max="150" value="{{ old('age', $notice->age ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Location / Native Place</label>
                    <input type="text" name="location" value="{{ old('location', $notice->location ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($notice) && $notice->date_of_birth ? $notice->date_of_birth->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Date of Death <span class="text-red-500">*</span></label>
                    <input type="date" name="date_of_death" required value="{{ old('date_of_death', isset($notice) && $notice->date_of_death ? $notice->date_of_death->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <label class="block text-sm font-medium mt-3 mb-1">Tribute Message</label>
            <textarea name="tribute_message" rows="5" class="w-full border rounded px-3 py-2 text-sm" placeholder="A short life summary or tribute...">{{ old('tribute_message', $notice->tribute_message ?? '') }}</textarea>

            <label class="block text-sm font-medium mt-3 mb-1">Survived By (family left behind)</label>
            <textarea name="survived_by" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="Spouse, children, etc.">{{ old('survived_by', $notice->survived_by ?? '') }}</textarea>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold text-sm mb-3">Funeral Details</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Funeral Date</label>
                    <input type="date" name="funeral_date" value="{{ old('funeral_date', isset($notice) && $notice->funeral_date ? $notice->funeral_date->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Funeral Time</label>
                    <input type="text" name="funeral_time" placeholder="e.g. 4.00 PM" value="{{ old('funeral_time', $notice->funeral_time ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>
            <label class="block text-sm font-medium mt-3 mb-1">Funeral Venue</label>
            <input type="text" name="funeral_venue" value="{{ old('funeral_venue', $notice->funeral_venue ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">

            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Contact Name</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $notice->contact_name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $notice->contact_phone ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Photo</label>
            <input type="file" name="photo" accept="image/*" class="w-full text-sm">
            @if(isset($notice) && $notice->photo)
                <img src="{{ asset('storage/'.$notice->photo) }}" class="mt-2 h-24 w-24 object-cover rounded-full border mx-auto">
            @endif
        </div>

        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                <option value="published" @selected(old('status', $notice->status ?? 'published') === 'published')>Published</option>
                <option value="draft" @selected(old('status', $notice->status ?? 'published') === 'draft')>Draft</option>
            </select>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Linked Video (optional)</label>
            <select name="video_id" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">— None —</option>
                @foreach($videos as $video)
                    <option value="{{ $video->id }}" @selected(old('video_id', $notice->video_id ?? '') == $video->id)>
                        {{ $video->title }}{{ $video->start_seconds ? ' ('.$video->formattedStartTime().')' : '' }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">If the video has a start timestamp set (in Videos → edit), playback will jump there automatically on the notice page.</p>
        </div>

        <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
            {{ isset($notice) ? 'Update Death Notice' : 'Add Death Notice' }}
        </button>
    </div>
</div>
