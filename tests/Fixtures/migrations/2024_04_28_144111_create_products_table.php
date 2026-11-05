<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 14, 2)->unsigned();
            $table->unsignedInteger('stock');
            $table->boolean('on_sale')->default(false);
            $table->json('features');
            $table->dateTime('published_at');
            $table->timestamp('expire_at');
            $table->date('released_on');
            $table->time('sale_time');
            $table->enum('status', ['draft', 'published']);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->year('manufactured_year');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
