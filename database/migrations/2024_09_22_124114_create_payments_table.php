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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->integer('amount');
            $table->string('payment_type');
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('water_bill')->nullable();
            $table->string('electricity_bill')->nullable();
            $table->string('water_consumption')->nullable();
            $table->string('electricity_consumption')->nullable();
            $table->string('rent_bill')->nullable();
            $table->foreign('tenant_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
