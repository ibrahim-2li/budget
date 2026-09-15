<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables whose amount column was created as a string.
     *
     * @var array<int, string>
     */
    private array $tables = ['incomes', 'expenses'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (DB::getDriverName() === 'pgsql') {
                // PostgreSQL cannot convert varchar to numeric without an explicit USING cast
                DB::statement("ALTER TABLE {$table} ALTER COLUMN amount TYPE numeric(12, 2) USING amount::numeric(12, 2)");

                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->decimal('amount', 12, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('amount')->change();
            });
        }
    }
};
