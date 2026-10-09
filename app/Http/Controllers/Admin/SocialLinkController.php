<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-social-links');
    }

    public function index()
    {
        $links = SocialLink::orderBy('order')->get();

        return view('admin.social-links.index', compact('links'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', 'in:facebook,youtube,instagram,tiktok,twitter_x,telegram,whatsapp,other'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $link = SocialLink::create($data);
        AuditLog::record('social_link.created', $link);

        return back()->with('success', 'Social link added.');
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $data = $request->validate([
            'platform' => ['required', 'in:facebook,youtube,instagram,tiktok,twitter_x,telegram,whatsapp,other'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $socialLink->update($data);
        AuditLog::record('social_link.updated', $socialLink);

        return back()->with('success', 'Social link updated.');
    }

    public function destroy(SocialLink $socialLink)
    {
        AuditLog::record('social_link.deleted', $socialLink, ['platform' => $socialLink->platform]);
        $socialLink->delete();

        return back()->with('success', 'Social link removed.');
    }
}
