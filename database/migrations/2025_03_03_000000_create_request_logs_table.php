<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('request-logger.table_name').'_fingerprints', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint', 50)->unique();
            $table->unsignedInteger('repeats')->default(0);
            $table->timestamps(6);
        });

        Schema::create(config('request-logger.table_name'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fingerprint_id')
                ->constrained(config('request-logger.table_name').'_fingerprints')
                ->cascadeOnDelete();
            $table->string('ip', 20);
            $table->string('host');
            $table->string('uri');
            $table->string('method', 20);
            $table->json('headers');
            $table->longText('payload')->nullable();
            $table->json('files')->nullable();
            $table->unsignedSmallInteger('response_status');
            $table->json('response_headers');
            $table->longText('response')->nullable();
            $table->json('custom_fields')->nullable();
            $table->integer('duration')->unsigned();
            $table->smallInteger('memory')->unsigned();
            $table->timestamp('sent_at', 6)->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('request-logger.table_name').'_fingerprints');
        Schema::dropIfExists(config('request-logger.table_name'));
    }
};
