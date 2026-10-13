<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('reference')->unique();
            $table->uuid('attempt');
            $table->string('customer_id')->nullable()->unique();
            $table->string('subscription_id')->nullable()->unique();
            $table->string('checkout_id')->nullable();
            $table->string('status')->default('none');
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('period_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_accounts');
    }
};
