<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 40);
            $table->string('apk_path')->unique();
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64);
            $table->text('release_notes')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('app_releases');
    }
};
