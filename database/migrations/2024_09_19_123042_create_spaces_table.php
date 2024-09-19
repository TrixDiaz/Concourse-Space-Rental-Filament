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
        Schema::create('spaces', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('concourse_id');
            $table->id();

            $table->string('name');
            $table->integer('price');
            $table->string('status');
            $table->boolean('is_active')->default(true);
            $table->integer('space_width');
            $table->integer('space_length');
            $table->float('space_area');
            $table->string('space_dimension');
            $table->integer('space_coordinates_x');
            $table->integer('space_coordinates_y');
            $table->integer('space_coordinates_x2');
            $table->integer('space_coordinates_y2');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('concourse_id')->references('id')->on('concourses')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
