<?php

namespace App\Http\Controllers;

use App\Models\DeathNotice;

class DeathNoticeController extends Controller
{
    public function index()
    {
        $notices = DeathNotice::published()->latest('date_of_death')->paginate(12);

        return view('frontend.death-notices-index', compact('notices'));
    }

    public function show(DeathNotice $deathNotice)
    {
        abort_unless($deathNotice->status === 'published', 404);

        $deathNotice->load('video');
        $deathNotice->incrementViews();

        $related = DeathNotice::published()
            ->where('id', '!=', $deathNotice->id)
            ->latest('date_of_death')
            ->limit(4)
            ->get();

        return view('frontend.death-notice-show', ['notice' => $deathNotice, 'related' => $related]);
    }
}
