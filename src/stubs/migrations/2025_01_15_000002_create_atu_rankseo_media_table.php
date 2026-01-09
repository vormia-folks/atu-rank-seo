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
        Schema::create('atu_rankseo_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slug_registry_id')->nullable()->comment('Optional association to vrm_slug_registry');
            $table->string('media_url')->comment('Unique media path/URL');
            $table->enum('media_type', ['image', 'file'])->default('image')->comment('Media type');
            $table->string('title')->nullable()->comment('Media title');
            $table->string('alt_text')->nullable()->comment('Alt text for images');
            $table->text('caption')->nullable()->comment('Media caption');
            $table->json('metadata')->nullable()->comment('Arbitrary metadata');
            $table->boolean('is_active')->default(true)->comment('Enable/disable this media SEO');
            $table->timestamps();

            $table->unique('media_url');
            $table->index('slug_registry_id');
            $table->index('media_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atu_rankseo_media');
    }
};
