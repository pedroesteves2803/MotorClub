<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 40);
            $table->string('title', 160);
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->string('email')->nullable();
            $table->string('status', 30)->default('received')->index();
            $table->text('internal_notes')->nullable();
            $table->json('technical_context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title', 160);
            $table->string('version', 30);
            $table->longText('content');
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('terms_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('version', 30);
            $table->timestamp('accepted_at');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'document_type', 'version']);
        });

        Schema::create('cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('visitor_id')->nullable()->index();
            $table->boolean('analytics')->default(false);
            $table->boolean('personalization')->default(false);
            $table->string('policy_version', 30);
            $table->timestamp('consented_at');
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
        Schema::dropIfExists('terms_acceptances');
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('feedbacks');
    }
};
