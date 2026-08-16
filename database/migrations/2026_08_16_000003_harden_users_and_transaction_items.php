<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->nullable()->after('item_type');
            $table->unsignedBigInteger('label_id')->nullable()->after('source_id');
            $table->index(['item_type', 'source_id'], 'acct_txn_items_source_idx');
        });

        if (Schema::hasColumn('users', 'plain_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('plain_password');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'plain_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('plain_password')->nullable()->after('password');
            });
        }

        Schema::table('accounting_transaction_items', function (Blueprint $table) {
            $table->dropIndex('acct_txn_items_source_idx');
            $table->dropColumn(['source_id', 'label_id']);
        });
    }
};
