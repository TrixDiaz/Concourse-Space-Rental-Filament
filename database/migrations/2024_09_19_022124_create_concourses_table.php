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
        Schema::create('concourses', function (Blueprint $table) {
            $table->unsignedBigInteger('rate_id');

            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->integer('spaces')->default(0);
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('total_monthly_rent')->nullable();
            $table->string('total_monthly_water')->nullable();
            $table->string('total_monthly_electricity')->nullable();
            $table->string('image')->nullable();
            $table->string('layout')->nullable();
            $table->integer('lease_term')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->foreign('rate_id')->references('id')->on('concourse_rates')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concourses');
    }
};
