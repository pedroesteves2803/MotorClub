<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('none');
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('period_end')->nullable();
            $table->timestamps();
        });
        Schema::create('mock_billing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_subscription_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->string('action');
            $table->integer('amount')->default(0);
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_billing_events');
        Schema::dropIfExists('mock_subscriptions');
    }
};
