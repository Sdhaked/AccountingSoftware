<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('mobile_number');
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email');
            $table->text('billing_address');
            $table->timestamps();
            $table->index(['company_id', 'name']);
        });

        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('percentage', 7, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('tax_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('default_rate', 14, 2);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('tax_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('price', 14, 2);
            $table->timestamps();
            $table->unique(['company_id', 'name']);
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->date('issued_at');
            $table->date('expires_at');
            $table->timestamps();
            $table->index(['expires_at', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('products');
        Schema::dropIfExists('services');
        Schema::dropIfExists('tax_classes');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('companies');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
