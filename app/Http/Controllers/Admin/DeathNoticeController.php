<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeathNotice;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeathNoticeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-death-notices');
    }

    public function index(Request $request)
    {
        $query = DeathNotice::query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $notices = $query->latest('date_of_death')->paginate(20)->withQueryString();

        return view('admin.death-notices.index', compact('notices'));
    }

    public function create()
    {
        $videos = Video::orderBy('title')->get();

        return view('admin.death-notices.create', compact('videos'));
    }

    public function store(Request $request)
    {
        $data = $this->validateNotice($request);
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('death-notices', 'public');
        }

        $notice = DeathNotice::create($data);
        AuditLog::record('death_notice.created', $notice, ['name' => $notice->name]);

        return redirect()->route('admin.death-notices.index')->with('success', 'Death notice added.');
    }

    public function edit(DeathNotice $deathNotice)
    {
        $videos = Video::orderBy('title')->get();

        return view('admin.death-notices.edit', ['notice' => $deathNotice, 'videos' => $videos]);
    }

    public function update(Request $request, DeathNotice $deathNotice)
    {
        $data = $this->validateNotice($request);

        if ($request->hasFile('photo')) {
            if ($deathNotice->photo) {
                Storage::disk('public')->delete($deathNotice->photo);
            }
            $data['photo'] = $request->file('photo')->store('death-notices', 'public');
        }

        $deathNotice->update($data);
        AuditLog::record('death_notice.updated', $deathNotice, ['name' => $deathNotice->name]);

        return redirect()->route('admin.death-notices.index')->with('success', 'Death notice updated.');
    }

    public function destroy(DeathNotice $deathNotice)
    {
        AuditLog::record('death_notice.deleted', $deathNotice, ['name' => $deathNotice->name]);
        $deathNotice->delete();

        return redirect()->route('admin.death-notices.index')->with('success', 'Death notice removed.');
    }

    private function validateNotice(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'location' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:date_of_death'],
            'date_of_death' => ['required', 'date'],
            'funeral_date' => ['nullable', 'date'],
            'funeral_time' => ['nullable', 'string', 'max:100'],
            'funeral_venue' => ['nullable', 'string', 'max:255'],
            'survived_by' => ['nullable', 'string', 'max:5000'],
            'tribute_message' => ['nullable', 'string', 'max:10000'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'video_id' => ['nullable', 'exists:videos,id'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }
}
