<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL/MariaDB only (raw ALTER ... MODIFY). Skipped on SQLite so the automated test suite can migrate.
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE advertisements MODIFY position ENUM(
            'header', 'top_banner', 'homepage_top', 'homepage_middle',
            'homepage_bottom', 'sidebar', 'article_top', 'article_middle',
            'article_bottom', 'between_cards', 'video_page', 'footer',
            'obituary_top', 'obituary_bottom'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE advertisements MODIFY position ENUM(
            'header', 'top_banner', 'homepage_top', 'homepage_middle',
            'homepage_bottom', 'sidebar', 'article_top', 'article_middle',
            'article_bottom', 'between_cards', 'video_page', 'footer'
        ) NOT NULL");
    }
};
