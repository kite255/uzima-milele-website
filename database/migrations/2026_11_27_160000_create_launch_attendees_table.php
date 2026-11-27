<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('launch_attendees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 12)->unique();
            $table->string('email')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('church', 150)->nullable();
            $table->string('college', 150)->nullable();
            $table->boolean('downloaded');
            $table->string('sms_status', 20)->default('pending');
            $table->string('email_status', 20)->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('launch_attendees'); }
};
