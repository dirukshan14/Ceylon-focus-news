<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-advertisements');
    }

    public function index(Request $request)
    {
        $query = Advertisement::query();

        if ($request->filled('position')) {
            $query->where('position', (string) $request->input('position'));
        }

        $ads = $query->latest()->paginate(20)->withQueryString();

        return view('admin.advertisements.index', compact('ads'));
    }

    public function create()
    {
        return view('admin.advertisements.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateAd($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }

        $data['created_by'] = $request->user()->id;

        $ad = Advertisement::create($data);
        AuditLog::record('advertisement.created', $ad);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement created.');
    }

    public function edit(Advertisement $advertisement)
    {
        return view('admin.advertisements.edit', ['ad' => $advertisement]);
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $data = $this->validateAd($request);

        if ($request->hasFile('image')) {
            if ($advertisement->image) {
                Storage::disk('public')->delete($advertisement->image);
            }
            $data['image'] = $request->file('image')->store('advertisements', 'public');
        }

        $advertisement->update($data);
        AuditLog::record('advertisement.updated', $advertisement);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement updated.');
    }

    public function destroy(Advertisement $advertisement)
    {
        AuditLog::record('advertisement.deleted', $advertisement, ['name' => $advertisement->name]);
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement deleted.');
    }

    public function toggle(Advertisement $advertisement)
    {
        $advertisement->update(['is_active' => ! $advertisement->is_active]);
        AuditLog::record('advertisement.toggled', $advertisement, ['is_active' => $advertisement->is_active]);

        return back()->with('success', 'Advertisement status updated.');
    }

    private function validateAd(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:image,html,adsense'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096', 'required_if:type,image'],
            'html_code' => ['nullable', 'string', 'max:20000', 'required_if:type,html,adsense'],
            'click_url' => ['nullable', 'url:http,https', 'max:500'],
            'position' => ['required', 'in:header,top_banner,homepage_top,homepage_middle,homepage_bottom,sidebar,article_top,article_middle,article_bottom,between_cards,video_page,footer'],
            'device_target' => ['required', 'in:all,desktop,tablet,mobile'],
            'is_active' => ['boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }
}
