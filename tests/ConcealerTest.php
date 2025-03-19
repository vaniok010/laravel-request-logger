<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Support\Concealer;
use Illuminate\Support\Facades\Config;

class ConcealerTest extends TestCase
{
    public function test_fields_can_be_hidden(): void
    {
        $replacer = Config::string('request-logger.hide.mask');

        $data = [
            'login' => 'login',
            'password' => 'pass',
            'data' => [
                [
                    'token' => 'token',
                ],
            ],
        ];

        $hide = [
            'password',
            'token',
        ];

        $replaced = [
            'login' => 'login',
            'password' => $replacer,
            'data' => [
                [
                    'token' => $replacer,
                ],
            ],
        ];

        $this->assertEquals($replaced, (new Concealer())->hide($data, $hide));
    }
}
