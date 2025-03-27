<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Generators\Tests\TestSupport\Enums\SingleCaseOrderStatuses;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('single_value_enum_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 4);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('product_slug');
            $table->nativeEnum('status', SingleCaseOrderStatuses::class)->index();

            $table->foreign('product_slug')
                ->references('slug')
                ->on('products')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
