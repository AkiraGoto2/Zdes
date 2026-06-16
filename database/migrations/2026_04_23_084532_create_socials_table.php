<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('socials', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
			$table->foreignId('event_id')->constrained()->cascadeOnDelete();;
			$table->string('platform');
			$table->string('url');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('socials');
    }
};
