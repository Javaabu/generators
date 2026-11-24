<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Auth\Schema\UserSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            UserSchema::columns($table);

            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
