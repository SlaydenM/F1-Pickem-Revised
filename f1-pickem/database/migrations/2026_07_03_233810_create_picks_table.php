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
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('team');
            $table->integer('number');
            $table->integer('position');
            $table->integer('session_key');
            $table->string('path');
            $table->timestamps();
        });
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('team');
            $table->integer('number');
            $table->integer('year');
            $table->timestamps();
        });
        Schema::create('picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(table: 'users', column: 'id')->onDelete('restrict');
            $table->float('score', 10, 3);
            $table->foreignId('d1_id')->constrained(table: 'drivers', column: 'id')->onDelete('restrict');
            $table->foreignId('d2_id')->constrained(table: 'drivers', column: 'id')->onDelete('restrict');
            $table->foreignId('d3_id')->constrained(table: 'drivers', column: 'id')->onDelete('restrict');
            $table->float('bonus');
            $table->integer('session_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picks');
        Schema::dropIfExists('winners');
        Schema::dropIfExists('drivers');
    }
};
