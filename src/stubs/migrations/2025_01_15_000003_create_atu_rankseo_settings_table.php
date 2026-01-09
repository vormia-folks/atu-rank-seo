<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('atu_rankseo_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true)->comment('Master on/off switch');
            $table->string('global_title')->nullable()->comment('Global default title');
            $table->text('global_description')->nullable()->comment('Global default description');
            $table->text('global_keywords')->nullable()->comment('Global default keywords');
            $table->json('dynamic_variables')->nullable()->comment('Key/value map for placeholder resolution');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atu_rankseo_settings');
    }
};
