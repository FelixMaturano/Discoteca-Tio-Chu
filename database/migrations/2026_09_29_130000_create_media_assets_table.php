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
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('filename');
            $table->string('path'); // e.g., 'images/LogoTioChu.png' or 'images/gallery/abc.webp'
            $table->string('category')->default('general'); // system, drinks, establishment, staff, general
            $table->boolean('is_system')->default(false);
            $table->string('system_key')->nullable()->unique(); // 'logo', 'login_background'
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('dimensions')->nullable(); // e.g. 1920x1080
            $table->string('mime_type')->nullable(); // image/png, image/jpeg, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
