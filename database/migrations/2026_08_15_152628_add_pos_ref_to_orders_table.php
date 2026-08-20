<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * pos_ref groups the multiple `orders` rows created by a single POS
     * checkout (one row per cart item) so they can be counted as ONE sale
     * / invoice instead of N. It stays null for orders created through the
     * normal "Add New Order" flow, since those are already one row = one
     * order/invoice.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'pos_ref')) {
            Schema::table('orders', function (Blueprint $table) {
                // Length capped at 191 (not the Laravel default 255) so the
                // index fits under MySQL/MariaDB's key-length limit when
                // using utf8mb4 (4 bytes/char): 191 * 4 = 764 bytes, under
                // the 767-byte cap older InnoDB configs enforce.
                $table->string('pos_ref', 191)->nullable()->after('note');
            });
        } else {
            // A previous failed run may have already created this column
            // at the default 255 length before the index step failed.
            // Force it down to 191 so the index below can succeed.
            DB::statement('ALTER TABLE `orders` MODIFY `pos_ref` VARCHAR(191) NULL');
        }

        if (! Schema::hasIndex('orders', 'orders_pos_ref_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('pos_ref');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pos_ref');
        });
    }
};