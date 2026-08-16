<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->nullable()->unique();
            $table->enum('type', ['income', 'expense']);
            $table->dateTime('occurred_at');
            $table->enum('source_type', ['company', 'personal'])->default('personal');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('issuer_name');
            $table->string('issuer_email')->nullable();
            $table->text('issuer_address')->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['type', 'occurred_at']);
            $table->index(['source_type', 'company_id']);
        });

        Schema::create('accounting_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_transaction_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['product', 'service', 'label', 'other']);
            $table->string('label');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('tax_rate', 7, 3)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->timestamps();
            $table->index('label');
        });

        Schema::create('accounting_transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_transaction_attachments');
        Schema::dropIfExists('accounting_transaction_items');
        Schema::dropIfExists('accounting_transactions');
    }
};
