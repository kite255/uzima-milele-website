<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devotions', function (Blueprint $table) {
            $table->text('feature_text')
                ->nullable()
                ->after('content');

            $table->text('lesson')
                ->nullable()
                ->after('feature_text');

            $table->string('scripture_reference')
                ->nullable()
                ->after('lesson');

            $table->text('scripture_text')
                ->nullable()
                ->after('scripture_reference');

            $table->text('ellen_white_quote')
                ->nullable()
                ->after('scripture_text');

            $table->string('ellen_white_reference')
                ->nullable()
                ->after('ellen_white_quote');
        });
    }

    public function down(): void
    {
        Schema::table('devotions', function (Blueprint $table) {
            $table->dropColumn([
                'feature_text',
                'lesson',
                'scripture_reference',
                'scripture_text',
                'ellen_white_quote',
                'ellen_white_reference',
            ]);
        });
    }
};