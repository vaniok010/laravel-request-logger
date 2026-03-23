<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $tableName = config('request-logger.table_name');

        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'ip')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->string('ip', 50)->change();
        });
    }

    public function down(): void
    {
        $tableName = config('request-logger.table_name');

        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, 'ip')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->string('ip', 20)->change();
        });
    }
};
