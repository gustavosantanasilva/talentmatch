<?php

declare(strict_types=1);

/**
 * Painel do organizador — login e logout.
 */

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Middleware\Auth;

require dirname(__DIR__, 2) . '/app/bootstrap.php';

// Logout
if (Request::input('logout') !== null) {
    Auth::logout();
    header('Location: ' . base_url('admin/'));
    exit;
}

// Já autenticado: vai para o painel
if (Auth::check()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$error = '';
$username = '';

if (Request::isPost() && Request::string('action') === 'login') {
    Csrf::validate();

    $username = Request::string('username');
    $password = (string) Request::input('password', '');

    $result = Auth::attempt($username, $password);

    if ($result['ok']) {
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    }

    $error = $result['message'];
}

View::render('admin/login', [
    'title' => 'Entrar',
    'error' => $error,
    'username' => $username,
], 'layouts/auth');
