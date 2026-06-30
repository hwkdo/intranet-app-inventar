<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intranet_app_inventar_meldungen', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('typ');
            $table->json('inventar');
            $table->json('data');
            $table->string('seventhings_status')->nullable();
            $table->text('seventhings_error')->nullable();
            $table->timestamp('seventhings_applied_at')->nullable();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intranet_app_inventar_meldungen');
    }
};
