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
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->date('lease_due')->nullable();
            $table->integer('lease_term')->nullable();
            $table->string('lease_status')->nullable();
            $table->json('bills')->nullable();
            $table->integer('monthly_payment')->nullable();
            $table->string('payment_status')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
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
