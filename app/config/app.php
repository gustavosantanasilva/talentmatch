<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Configuração geral da aplicação.
 * Valores sensíveis NÃO ficam aqui — eles vêm do arquivo .env.
 */
return [
    'name' => 'TalentMatch',
    'tagline' => 'Conectando talentos, oportunidades e inteligência artificial.',
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::bool('APP_DEBUG', false),
    'url' => rtrim((string) Env::get('APP_URL', 'http://localhost:8000'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'America/Sao_Paulo'),
    'session_name' => Env::get('APP_SESSION_NAME', 'talentmatch_session'),

    // Regras do jogo
    'game' => [
        'max_jobs' => 4,
        'min_players' => 1,
        'nickname_min' => 2,
        'nickname_max' => 30,
        'states' => ['WAITING', 'PROFILES', 'GENERATING', 'JOBS', 'RESULTS', 'FINISHED'],
    ],

    // Áreas profissionais disponíveis
    'areas' => [
        'software' => 'Desenvolvimento de software',
        'ti' => 'Tecnologia da informação',
        'suporte' => 'Suporte técnico',
        'design' => 'Design',
        'administracao' => 'Administração',
        'marketing' => 'Marketing',
        'dados' => 'Dados',
        'outras' => 'Outras áreas',
    ],

    // Níveis de habilidade
    'skill_levels' => [
        'BASIC' => 'Básico',
        'INTERMEDIATE' => 'Intermediário',
        'ADVANCED' => 'Avançado',
    ],

    // Avatares disponíveis (emoji) e cores de fundo correspondentes.
    'avatars' => ['🦊', '🐼', '🐸', '🦁', '🐯', '🐨', '🐙', '🦄', '🐝', '🦖', '🐳', '🦉', '🐧', '🐢', '🐬', '🐥'],

    'avatar_colors' => [
        '#087F5B',
        '#164E63',
        '#C69B52',
        '#DC2626',
        '#D97706',
        '#DB2777',
        '#0D9488',
        '#2563EB',
    ],

    'default_avatar' => '🙂',
];
