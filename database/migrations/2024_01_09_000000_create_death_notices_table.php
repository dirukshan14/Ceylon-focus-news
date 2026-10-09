<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_notices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('photo')->nullable();

            $table->unsignedInteger('age')->nullable();
            $table->string('location')->nullable(); // native place / village / city
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_death');

            // Funeral / burial details
            $table->date('funeral_date')->nullable();
            $table->string('funeral_time')->nullable(); // free text, e.g. "4.00 PM"
            $table->string('funeral_venue')->nullable();

            $table->text('survived_by')->nullable();     // spouse/children/family left behind
            $table->text('tribute_message')->nullable();  // life summary / tribute

            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();

            // Optional link to a recorded/livestreamed funeral service video,
            // with an automatic start timestamp (see videos.start_seconds).
            $table->foreignId('video_id')->nullable()->constrained('videos')->nullOnDelete();

            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedBigInteger('views_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'date_of_death']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_notices');
    }
};
