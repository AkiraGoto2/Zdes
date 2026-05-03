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
       Schema::create('events', function (Blueprint $table) {
			$table->id();
			$table->timestamps();

			$table->foreignId('user_id')->constrained();
			$table->foreignId('category_id')->constrained();

			$table->string('name');
			$table->dateTime('event_date');
			$table->enum('age', ['0+', '6+', '12+', '16+', '18+']);
			$table->text('description');
			$table->unsignedInteger('price')->nullable();
			$table->string('address');

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
