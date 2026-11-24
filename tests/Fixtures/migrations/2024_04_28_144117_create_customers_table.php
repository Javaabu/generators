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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('last_login_at')->nullable()->index();
            $table->unsignedInteger('login_attempts')->nullable();
            $table->boolean('require_password_update')->default(false);
            $table->string('status')->index();
            $table->string('new_email')->index()->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->string('designation')->unique();
            $table->string('address');
            $table->boolean('on_sale')->default(false);
            $table->timestamp('expire_at');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
