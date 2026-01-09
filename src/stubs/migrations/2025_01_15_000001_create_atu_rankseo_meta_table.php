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
        Schema::create('atu_rankseo_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slug_registry_id')->nullable()->comment('FK to vrm_slug_registry');
            $table->enum('type', ['page'])->default('page')->comment('SEO type');
            $table->string('title')->nullable()->comment('Resolved page title');
            $table->text('description')->nullable()->comment('Resolved meta description');
            $table->text('keywords')->nullable()->comment('Resolved meta keywords');
            $table->string('canonical_url')->nullable()->comment('Canonical URL');
            $table->string('robots')->nullable()->comment('Robots meta tag');
            $table->boolean('use_global')->default(true)->comment('Whether to merge with global SEO');
            $table->boolean('is_active')->default(true)->comment('Enable/disable this SEO entry');
            $table->timestamps();

            $table->index('slug_registry_id');
            $table->index('type');
            $table->index('is_active');
            $table->unique(['slug_registry_id', 'type'], 'atu_rankseo_meta_slug_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atu_rankseo_meta');
    }
};
