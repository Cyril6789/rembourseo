<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * FAMILIES
         */
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('family_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner','member','viewer'])->default('member');
            $table->timestamps();
            $table->unique(['family_id','user_id']);
            $table->index(['user_id','role']);
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('token', 120)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['family_id','expires_at']);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['parent','enfant'])->index();
            $table->string('first_name', 80);
            $table->string('last_name', 120)->nullable();
            $table->date('birthdate')->nullable();
            $table->json('identifiers')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['family_id','type','last_name']);
        });

        Schema::create('insurers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->json('contact_info')->nullable();
            $table->json('credentials_mask')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['family_id','name']);
        });

        Schema::create('user_insurers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->string('policy_number', 120)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_id','order']);
            $table->index(['insurer_id','order']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['carte','cheque','virement','especes'])->index();
            $table->string('label', 120);
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last4', 4)->nullable();
            $table->string('iban_mask', 34)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['family_id','type','is_active']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date')->index();
            $table->string('provider', 160)->nullable();
            $table->decimal('amount_total', 12, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['draft','tracking','closed'])->default('tracking')->index();
            $table->string('reference', 140)->nullable();
            $table->text('notes')->nullable();
            $table->json('breakdown')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['family_id','status','date']);
        });

        Schema::create('expense_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('disk', 40)->default('private');
            $table->string('mime', 100)->nullable();
            $table->string('original_name', 180)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->index(['expense_id','mime']);
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->enum('status', ['pending','submitted','partially_reimbursed','reimbursed','rejected'])
                  ->default('pending')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['expense_id','order']);
            $table->index(['insurer_id','status']);
        });

        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('received_at')->index();
            $table->string('currency', 3)->default('EUR');
            $table->string('credited_account', 160)->nullable();
            $table->string('reference', 160)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['claim_id','received_at']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->timestamps();
            $table->unique(['family_id','name']);
        });

        Schema::create('expense_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['expense_id','tag_id']);
            $table->index(['tag_id']);
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('provider_user_id', 191);
            $table->string('email')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['provider','provider_user_id']);
            $table->index(['user_id','provider']);
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('auditable');
            $table->string('action', 80);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['family_id','action','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('expense_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('reimbursements');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('expense_files');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('user_insurers');
        Schema::dropIfExists('insurers');
        Schema::dropIfExists('members');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('family_user');
        Schema::dropIfExists('families');
    }
};
