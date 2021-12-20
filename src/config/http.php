<?php
return [
    'default' => [
        'gateway_url' => env('DEFAULT_HTTP_GATEWAY_URL', 'http://localhost:8081'), //服务地址
        'debug'       => env('DEFAULT_HTTP_DEBUG', false),
        'timeout'     => env('DEFAULT_HTTP_TIMEOUT', 5), // 全局超时时长
        'retry'       => env('DEFAULT_HTTP_RETRY', 0), // 全局重试次数
        'sleep'       => env('DEFAULT_HTTP_SLEEP', 0), // 睡眠时间,只有开启重试后会启用
    ],
];
