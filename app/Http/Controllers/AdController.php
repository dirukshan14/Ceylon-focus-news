<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;

class AdController extends Controller
{
    public function click(Advertisement $advertisement)
    {
        $advertisement->recordClick();

        // SECURITY: only ever redirect to http(s) destinations (no javascript:/data: URLs).
        $target = (string) $advertisement->click_url;
        if (! preg_match('#^https?://#i', $target)) {
            $target = url('/');
        }

        return redirect()->away($target);
    }
}
