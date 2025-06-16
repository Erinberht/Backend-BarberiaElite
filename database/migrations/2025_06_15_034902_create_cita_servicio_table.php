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
        Schema::create('cita_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id');
            $table->unsignedBigInteger('servicio_id');
            $table->time('hora'); // puedes usar 'time' si manejas hora exacta
            $table->timestamps();

            $table->foreign('cita_id')->references('id')->on('citas')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cita_servicio');
    }
};
