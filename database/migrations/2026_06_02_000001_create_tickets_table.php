<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code')->unique();  
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('price_paid');    
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone')->nullable();
            $table->enum('status', ['active', 'cancelled'])->default('active');
            $table->boolean('reminder_sent')->default(false); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
