<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Database\Factories;

use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Models\RequestLogFingerprint;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestLogFactory extends Factory
{
    protected $model = RequestLog::class;

    public function definition(): array
    {
        return [
            'fingerprint_id' => RequestLogFingerprint::factory(),
            'ip' => $this->faker->ipv4(),
            'host' => $this->faker->domainName(),
            'uri' => $this->faker->url(),
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
            'headers' => [['accept' => 'application/json']],
            'payload' => json_encode([['field' => 'value']]),
            'response_status' => rand(200, 500),
            'response_headers' => [['content-type' => 'application/json']],
            'response' => json_encode([['filed' => 'value']]),
            'custom_fields' => [['custom_field' => 'custom_value']],
            'duration' => rand(1, 1000),
            'memory' => rand(1, 1000),
            'timezone' => $this->faker->timezone(),
            'sent_at' => $this->faker->dateTime(),
        ];
    }
}
