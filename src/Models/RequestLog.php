<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Models;

use Hryha\RequestLogger\Builders\RequestLogBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class RequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'fingerprint_id',
        'ip',
        'host',
        'uri',
        'method',
        'headers',
        'payload',
        'files',
        'response_status',
        'response_headers',
        'response',
        'custom_fields',
        'duration',
        'memory',
        'timezone',
        'sent_at',
    ];

    protected $appends = [
        'decoded_uri',
        'formatted_date',
        'formatted_time',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'headers' => 'json',
            'files' => 'json',
            'response_headers' => 'json',
            'custom_fields' => 'json',
            'sent_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    public function getTable(): string
    {
        return Config::string('request-logger.table_name', parent::getTable());
    }

    /**
     * @param Builder $query
     * @return RequestLogBuilder<RequestLog>
     */
    public function newEloquentBuilder($query): RequestLogBuilder
    {
        return new RequestLogBuilder($query);
    }

    public function fingerprint(): BelongsTo
    {
        return $this->belongsTo(RequestLogFingerprint::class, 'fingerprint_id');
    }

    protected function decodedUri(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!isset($attributes['uri']) || !is_string($attributes['uri'])) {
                    return null;
                }

                $parsedUri = parse_url(urldecode($attributes['uri']));
                $uri = $parsedUri['path'] ?? '';
                if (isset($parsedUri['query'])) {
                    $uri .= "?{$parsedUri['query']}";
                }

                return $uri;
            },
        );
    }

    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!is_string($attributes['sent_at']) && !$attributes['sent_at'] instanceof Carbon) {
                    return $attributes['sent_at'];
                }

                if (!is_string($attributes['timezone'])) {
                    return $attributes['sent_at'];
                }

                return Carbon::parse($attributes['sent_at'])
                    ->setTimezone($attributes['timezone'])
                    ->format(Config::string('request-logger.date_format'));
            },
        );
    }

    protected function formattedTime(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!is_string($attributes['sent_at']) && !$attributes['sent_at'] instanceof Carbon) {
                    return $attributes['sent_at'];
                }

                if (!is_string($attributes['timezone'])) {
                    return $attributes['sent_at'];
                }

                return Carbon::parse($attributes['sent_at'])
                    ->setTimezone($attributes['timezone'])
                    ->format(Config::string('request-logger.time_format'));
            },
        );
    }
}
