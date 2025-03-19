<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Database\Factories;

use Hryha\RequestLogger\Models\RequestLogFingerprint;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestLogFingerprintFactory extends Factory
{
    protected $model = RequestLogFingerprint::class;

    public function definition(): array
    {
        return [
            'fingerprint' => uniqid(),
            'repeats' => rand(1, 1000),
        ];
    }
}
