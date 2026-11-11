<?php

declare(strict_types=1);

/**
 * Teste de fumaça HTTP — exercita os fluxos reais do painel via cURL.
 *
 * Uso:
 *   php -S 127.0.0.1:8080 -t public   (em outro terminal)
 *   php tests/smoke.php [http://127.0.0.1:8080]
 *
 * Verifica login, criação de sala, QR/URL, endpoints da API e proteções
 * (autenticação e CSRF). Remove as salas criadas ao final.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$cookieJar = sys_get_temp_dir() . '/tm_smoke_' . getmypid() . '.txt';
@unlink($cookieJar);

$createdRoomIds = [];
$passed = 0;
$failed = 0;

/**
 * @return array{status:int,body:string,headers:array<string,string>,location:?string}
 */
function http(string $method, string $path, array $data = [], array $options = []): array
{
    global $base, $cookieJar;

    $ch = curl_init();
    $url = $base . $path;

    $headers = ['Accept: text/html,application/json'];

    if (!empty($options['csrf'])) {
        $headers[] = 'X-CSRF-Token: ' . $options['csrf'];
        $headers[] = 'X-Requested-With: XMLHttpRequest';
    }

    if ($method === 'POST') {
        if (!empty($options['json'])) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 15,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    }

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    if ($response === false) {
        return ['status' => 0, 'body' => '', 'headers' => [], 'location' => null];
    }

    $rawHeaders = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    $parsed = [];
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $parsed[strtolower(trim($k))] = trim($v);
        }
    }

    return ['status' => $status, 'body' => $body, 'headers' => $parsed, 'location' => $parsed['location'] ?? null];
}

function check(string $name, bool $condition, string $detail = ''): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "  \033[32m✓\033[0m {$name}\n";
    } else {
        $failed++;
        echo "  \033[31m✗\033[0m {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

function extractCsrf(string $html): string
{
    if (preg_match('/name="csrf-token"\s+content="([a-f0-9]+)"/', $html, $m) === 1) {
        return $m[1];
    }
    if (preg_match('/name="csrf_token"\s+value="([a-f0-9]+)"/', $html, $m) === 1) {
        return $m[1];
    }

    return '';
}

echo "\n\033[1mTalentMatch — Teste de fumaça HTTP\033[0m\n";
echo "Servidor: {$base}\n\n";

// ---------------------------------------------------------------------------
echo "\033[1;36mLogin do organizador\033[0m\n";

$login = http('GET', '/admin/');
check('A página de login responde 200', $login['status'] === 200, "status {$login['status']}");
check('A página de login contém o formulário', str_contains($login['body'], 'Painel do organizador'));

$csrf = extractCsrf($login['body']);
check('O token CSRF foi emitido', $csrf !== '');

$badLogin = http('POST', '/admin/', ['action' => 'login', 'username' => 'admin', 'password' => 'errada', 'csrf_token' => $csrf]);
check('Senha incorreta não autentica', $badLogin['status'] === 200 && str_contains($badLogin['body'], 'inválidos'));

$goodLogin = http('POST', '/admin/', ['action' => 'login', 'username' => 'admin', 'password' => 'talentmatch', 'csrf_token' => $csrf]);
check('Login válido redireciona (302)', $goodLogin['status'] === 302, "status {$goodLogin['status']}");
check('Redireciona para o dashboard', str_contains((string) $goodLogin['location'], 'dashboard.php'));

// ---------------------------------------------------------------------------
echo "\n\033[1;36mCriação de sala\033[0m\n";

$dashboard = http('GET', '/admin/dashboard.php');
check('O dashboard responde 200', $dashboard['status'] === 200, "status {$dashboard['status']}");
check('O dashboard exibe a visão geral', str_contains($dashboard['body'], 'Suas salas'));

$csrfDash = extractCsrf($dashboard['body']);
$create = http('POST', '/admin/dashboard.php', ['action' => 'create_room', 'csrf_token' => $csrfDash]);
check('Criar sala redireciona (302)', $create['status'] === 302, "status {$create['status']}");

$location = (string) $create['location'];
check('Redireciona para a sala criada', str_contains($location, 'sala.php?id='));

$roomId = 0;
if (preg_match('/id=(\d+)/', $location, $m) === 1) {
    $roomId = (int) $m[1];
}
check('O identificador da sala foi extraído', $roomId > 0);
$createdRoomIds[] = $roomId;

$sala = http('GET', '/admin/sala.php?id=' . $roomId);
check('A página da sala responde 200', $sala['status'] === 200, "status {$sala['status']}");
check('A sala exibe o QR Code', str_contains($sala['body'], 'id="qrcode"'));

$code = '';
if (preg_match('/class="tm-room-code tm-mb-0">([A-Z0-9]{6})</', $sala['body'], $m) === 1) {
    $code = $m[1];
}
check('O código da sala (6 caracteres) é exibido', $code !== '', 'não encontrado');
check('A URL de entrada aparece na página', str_contains($sala['body'], 'entrar.php?room=' . $code));

// ---------------------------------------------------------------------------
echo "\n\033[1;36mEndpoints da API\033[0m\n";

$state = http('GET', '/api/room/state.php?code=' . $code);
$stateJson = json_decode($state['body'], true);
check('GET /api/room/state.php responde 200', $state['status'] === 200, "status {$state['status']}");
check('A resposta tem o formato padronizado', is_array($stateJson) && ($stateJson['success'] ?? false) === true);
check('O estado retornado é WAITING', ($stateJson['data']['status'] ?? '') === 'WAITING');
check('O payload do dono inclui join_url', isset($stateJson['data']['join_url']));

$missing = http('GET', '/api/room/state.php?code=ZZZZZZ');
check('Sala inexistente retorna 404', $missing['status'] === 404, "status {$missing['status']}");

$csrfApi = extractCsrf(http('GET', '/admin/dashboard.php')['body']);
$apiCreate = http('POST', '/api/room/create.php', [], ['csrf' => $csrfApi, 'json' => true]);
$apiJson = json_decode($apiCreate['body'], true);
check('POST /api/room/create.php responde 201', $apiCreate['status'] === 201, "status {$apiCreate['status']}");
check('A API retorna o código da nova sala', !empty($apiJson['data']['code']));
if (!empty($apiJson['data']['id'])) {
    $createdRoomIds[] = (int) $apiJson['data']['id'];
}

$noCsrf = http('POST', '/api/room/create.php', [], ['json' => true]);
check('Criar sala sem CSRF retorna 419', $noCsrf['status'] === 419, "status {$noCsrf['status']}");

// ---------------------------------------------------------------------------
echo "\n\033[1;36mProteção de acesso\033[0m\n";

$anonJar = sys_get_temp_dir() . '/tm_anon_' . getmypid() . '.txt';
@unlink($anonJar);
$savedJar = $cookieJar;
$cookieJar = $anonJar;

$anonCreate = http('POST', '/api/room/create.php', [], ['json' => true]);
check('Criar sala sem autenticação retorna 401', $anonCreate['status'] === 401, "status {$anonCreate['status']}");

$anonDashboard = http('GET', '/admin/dashboard.php');
check('Dashboard sem login redireciona (302)', $anonDashboard['status'] === 302, "status {$anonDashboard['status']}");
check('Redireciona para o login', str_contains((string) $anonDashboard['location'], 'admin'));

$cookieJar = $savedJar;
@unlink($anonJar);

// ---------------------------------------------------------------------------
echo "\n\033[1;36mLimpeza\033[0m\n";

$pdo = Database::connection();
foreach (array_unique($createdRoomIds) as $id) {
    if ($id > 0) {
        $pdo->prepare('DELETE FROM rooms WHERE id = :id')->execute(['id' => $id]);
    }
}
check('As salas de teste foram removidas', true);

@unlink($cookieJar);

echo "\n" . str_repeat('─', 64) . "\n";
printf("  Verificações: %d  |  \033[32mPassou: %d\033[0m  |  \033[31mFalhou: %d\033[0m\n", $passed + $failed, $passed, $failed);
echo str_repeat('─', 64) . "\n";

exit($failed === 0 ? 0 : 1);
