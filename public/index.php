<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request; // <--- ВАЖНО: Трябва да добавиш това

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    // --- ТОВА Е ДОБАВКАТА ЗА KOYEB (TRUSTED PROXIES) ---
    // Това казва на Symfony да вярва на Koyeb Load Balancer-а
    Request::setTrustedProxies(
        ['0.0.0.0/0', '::/0'], // 0.0.0.0/0 значи "вярвай на всички проксита"
        Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
    );
    // ----------------------------------------------------

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};