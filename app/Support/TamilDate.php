<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class TamilDate
{
    private const WEEKDAYS = [
        1 => 'திங்கள்', 2 => 'செவ்வாய்', 3 => 'புதன்', 4 => 'வியாழன்',
        5 => 'வெள்ளி', 6 => 'சனி', 7 => 'ஞாயிறு',
    ];

    private const MONTHS = [
        1 => 'ஜனவரி', 2 => 'பிப்ரவரி', 3 => 'மார்ச்', 4 => 'ஏப்ரல்',
        5 => 'மே', 6 => 'ஜூன்', 7 => 'ஜூலை', 8 => 'ஆகஸ்ட்',
        9 => 'செப்டம்பர்', 10 => 'அக்டோபர்', 11 => 'நவம்பர்', 12 => 'டிசம்பர்',
    ];

    /**
     * e.g. "செவ்வாய், 15 செப்டம்பர் 2026"
     */
    public static function format(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();

        return self::WEEKDAYS[(int) $date->format('N')].', '
            .$date->format('d').' '
            .self::MONTHS[(int) $date->format('n')].' '
            .$date->format('Y');
    }
}
