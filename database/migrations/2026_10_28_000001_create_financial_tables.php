<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 20);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_active', 'start_date']);
        });

        Schema::create('fixed_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            $table->string('category', 30);
            $table->string('frequency', 20);
            $table->unsignedTinyInteger('payment_day')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_active', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_expenses');
        Schema::dropIfExists('income_sources');
    }
};
