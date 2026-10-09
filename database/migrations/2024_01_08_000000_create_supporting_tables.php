<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site-wide social links, shown in header/footer
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'youtube', 'instagram', 'tiktok', 'twitter_x', 'telegram', 'whatsapp', 'other']);
            $table->string('url');
            $table->string('icon')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Centralized media library
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');   // image/jpeg, video/mp4, etc.
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Key/value site settings (general, SEO, appearance, ads defaults)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general'); // general, seo, social, appearance, ads
            $table->timestamps();
        });

        // Breaking news ticker items (separate from full articles for flexibility)
        Schema::create('breaking_news', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url')->nullable(); // used if not linked to an article
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'priority']);
        });

        // Audit trail for important admin actions
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');            // e.g. "article.published"
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('breaking_news');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('media');
        Schema::dropIfExists('social_links');
    }
};
