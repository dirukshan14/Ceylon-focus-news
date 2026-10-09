<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['image', 'html', 'adsense'])->default('image');

            $table->string('image')->nullable();
            $table->text('html_code')->nullable();
            $table->string('click_url')->nullable();

            $table->enum('position', [
                'header', 'top_banner', 'homepage_top', 'homepage_middle',
                'homepage_bottom', 'sidebar', 'article_top', 'article_middle',
                'article_bottom', 'between_cards', 'video_page', 'footer',
            ]);

            $table->enum('device_target', ['all', 'desktop', 'tablet', 'mobile'])->default('all');

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['position', 'is_active', 'device_target']);
        });

        // Fine-grained daily logs, used to compute CTR trends on the dashboard.
        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['advertisement_id', 'date']);
        });

        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['advertisement_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
        Schema::dropIfExists('ad_impressions');
        Schema::dropIfExists('advertisements');
    }
};
