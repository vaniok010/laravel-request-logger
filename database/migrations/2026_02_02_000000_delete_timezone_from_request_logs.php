<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable(config('request-logger.table_name'))) {
            return;
        }

        Schema::table(config('request-logger.table_name'), function (Blueprint $table): void {
            $table->dropColumn('timezone');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(config('request-logger.table_name'))) {
            return;
        }

        Schema::table(config('request-logger.table_name'), function (Blueprint $table): void {
            $table->string('timezone', 50)->after('memory');
        });
    }
};
