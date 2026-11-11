<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Configuração da inteligência artificial (DeepSeek).
 *
 * A chave de API fica exclusivamente no servidor, carregada a partir do .env.
 * Nunca é enviada ao navegador, ao HTML, ao JavaScript ou a endpoints públicos.
 */
return [
    'provider' => 'deepseek',
    'api_key' => Env::get('DEEPSEEK_API_KEY', ''),
    'base_url' => rtrim((string) Env::get('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'), '/'),
    'model' => Env::get('DEEPSEEK_MODEL', 'deepseek-chat'),
    'timeout' => Env::int('DEEPSEEK_TIMEOUT', 30),
    'max_tokens' => Env::int('DEEPSEEK_MAX_TOKENS', 2400),
    'temperature' => 0.8,
];
