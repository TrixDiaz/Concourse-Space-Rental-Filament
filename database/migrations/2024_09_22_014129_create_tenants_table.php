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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('concourse_id');
            $table->unsignedBigInteger('space_id');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('tenant_id')->references('id')->on('users');
            $table->foreign('concourse_id')->references('id')->on('concourses');
            $table->foreign('space_id')->references('id')->on('spaces');
            $table->foreign('owner_id')->references('id')->on('users');

            $table->softDeletes();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
