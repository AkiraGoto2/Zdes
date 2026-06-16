<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();

            $table->string('name');
            $table->dateTime('event_date');
            $table->enum('age', ['0+', '6+', '12+', '16+', '18+']);
            $table->text('description');

            // Цена
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('price_to')->nullable();

            // Адрес и координаты
            $table->string('address');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // Модерация
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();

            // Контакты организатора
            $table->string('contact_phone', 100)->nullable();
            $table->string('contact_site', 255)->nullable();
            $table->string('contact_telegram', 255)->nullable();
            $table->string('contact_vk', 255)->nullable();

            // Доп. поля
            $table->unsignedInteger('max_participants')->nullable();
            $table->unsignedInteger('kudago_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
