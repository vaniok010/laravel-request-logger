<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;

class RequestLogFingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'fingerprint',
        'repeats',
    ];

    public function getTable(): string
    {
        return Config::string('request-logger.table_name', parent::getTable()).'_fingerprints';
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(RequestLog::class, 'fingerprint_id');
    }
}
