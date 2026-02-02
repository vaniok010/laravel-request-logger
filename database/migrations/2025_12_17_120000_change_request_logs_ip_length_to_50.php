<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable(config('request-logger.table_name'))) {
            return;
        }

        $length = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', config('request-logger.table_name'))
            ->where('COLUMN_NAME', 'ip')
            ->value('CHARACTER_MAXIMUM_LENGTH');

        if (null !== $length && (int)$length >= 50) {
            return;
        }

        DB::statement('ALTER TABLE `'.config('request-logger.table_name').'` MODIFY `ip` VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('request_logs')) {
            return;
        }

        DB::statement('ALTER TABLE `'.config('request-logger.table_name').'` MODIFY `ip` VARCHAR(20) NOT NULL');
    }
};
