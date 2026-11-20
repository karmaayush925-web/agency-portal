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
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('status');
            }
            if (! Schema::hasColumn('invoices', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = array_filter(
                ['transaction_id', 'paid_at'],
                fn ($col) => Schema::hasColumn('invoices', $col)
            );
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};