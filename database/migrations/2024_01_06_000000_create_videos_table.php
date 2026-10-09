<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('source_url');   // raw URL admin pasted in
            $table->string('embed_url')->nullable(); // computed embeddable URL
            $table->enum('provider', ['youtube', 'facebook', 'instagram', 'tiktok', 'vimeo', 'mp4', 'other'])
                ->default('other');
            $table->string('thumbnail')->nullable();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();

            // Optionally link a video to a specific article (embed within article)
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('tag_video', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['video_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_video');
        Schema::dropIfExists('videos');
    }
};
