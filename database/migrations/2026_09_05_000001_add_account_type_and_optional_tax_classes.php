<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('labels', 'type')) {
            Schema::table('labels', function (Blueprint $table) {
                $table->enum('type', ['income', 'expense'])->default('expense')->after('id');
            });
        }

        if (Schema::hasTable('accounting_transaction_items') && Schema::hasTable('accounting_transactions')) {
            DB::table('labels')
                ->whereIn('id', function ($query) {
                    $query->select('accounting_transaction_items.label_id')
                        ->from('accounting_transaction_items')
                        ->join('accounting_transactions', 'accounting_transactions.id', '=', 'accounting_transaction_items.accounting_transaction_id')
                        ->where('accounting_transactions.type', 'income')
                        ->whereNotNull('accounting_transaction_items.label_id');
                })
                ->update(['type' => 'income']);
        }

        if (! Schema::hasColumn('services', 'tax_class_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->foreignId('tax_class_id')->nullable()->after('company_id')
                    ->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('products', 'tax_class_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['tax_class_id']);
                $table->foreignId('tax_class_id')->nullable()->change();
                $table->foreign('tax_class_id')->references('id')->on('tax_classes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'tax_class_id')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tax_class_id');
            });
        }

        if (Schema::hasColumn('labels', 'type')) {
            Schema::table('labels', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasColumn('products', 'tax_class_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['tax_class_id']);
                $table->foreignId('tax_class_id')->nullable(false)->change();
                $table->foreign('tax_class_id')->references('id')->on('tax_classes')->restrictOnDelete();
            });
        }
    }
};
