# API interna — TalentMatch

A API interna é composta por endpoints PHP que respondem **JSON padronizado**. Ela é
usada pelo painel administrativo, pela interface dos participantes e pelo telão.

## 1. Formato das respostas

**Sucesso**

```json
{
  "success": true,
  "message": "Operação realizada com sucesso.",
  "data": {}
}
```

**Erro**

```json
{
  "success": false,
  "message": "Não foi possível realizar a operação.",
  "data": null
}
```

**Erro de validação** (`HTTP 422`)

```json
{
  "success": false,
  "message": "Dados inválidos.",
  "data": { "errors": { "nickname": "Apelido é obrigatório." } }
}
```

Regras gerais:

- Métodos HTTP explícitos; requisições de escrita usam `POST`.
- Envio em `application/json` ou `application/x-www-form-urlencoded`.
- Cabeçalho `X-CSRF-Token` obrigatório nas requisições de escrita.
- Nunca há *stack trace* nem dados internos na resposta.

## 2. Endpoints

> **Status:** os endpoints de salas e participantes (fases 2 e 3) estão
> **implementados**. Os demais serão adicionados nas fases seguintes.

### Salas

| Método | Rota | Descrição | Fase | Status |
|---|---|---|:--:|:--:|
| POST | `/api/room/create.php` | Cria uma sala | 2 | ✅ |
| POST | `/api/room/join.php` | Entra em uma sala com apelido | 3 | ✅ |
| GET | `/api/room/state.php` | Estado atual da sala | 2 | ✅ |
| GET | `/api/room/participants.php` | Lista pública dos participantes | 3 | ✅ |

#### `POST /api/room/join.php`

Corpo: `{ "room": "ABC123", "nickname": "Fulano" }`. Exige token CSRF.
Resposta `201` com `data.token` (texto puro, exibido **apenas uma vez**) — no servidor
é guardado somente o hash SHA-256.

Erros: `404` (sala inexistente), `409` (partida já começou ou apelido em uso),
`422` (apelido inválido), `419` (CSRF).

### Partida

| Método | Rota | Descrição | Fase |
|---|---|---|:--:|
| POST | `/api/game/start.php` | Inicia a partida | 2 |
| POST | `/api/game/next-job.php` | Avança para a próxima vaga | 6 |
| POST | `/api/game/finish.php` | Encerra a partida | 8 |
| GET | `/api/game/state.php` | Estado atual da partida | 3 |

### Perfis

| Método | Rota | Descrição | Fase |
|---|---|---|:--:|
| POST | `/api/profile/save.php` | Salva o perfil | 4 |
| POST | `/api/profile/complete.php` | Finaliza o perfil | 4 |

### Vagas

| Método | Rota | Descrição | Fase |
|---|---|---|:--:|
| POST | `/api/jobs/generate.php` | Gera as vagas (IA ou fallback) | 5 |
| GET | `/api/jobs/current.php` | Vaga atual | 6 |

### Candidaturas

| Método | Rota | Descrição | Fase |
|---|---|---|:--:|
| POST | `/api/applications/submit.php` | Candidatar-se ou pular | 6 |

### Resultados

| Método | Rota | Descrição | Fase |
|---|---|---|:--:|
| POST | `/api/results/calculate.php` | Calcula a compatibilidade | 7 |
| GET | `/api/results/rankings.php` | Rankings por vaga | 7 |
| GET | `/api/results/top10.php` | Top 10 geral | 7 |

## 3. Autenticação e autorização

- **Painel administrativo:** sessão PHP autenticada + token CSRF.
- **Participantes:** token aleatório enviado no cabeçalho `X-Participant-Token`
  (armazenado no servidor apenas como hash).
- **Telão:** somente leitura, expõe apenas dados agregados (nunca currículos completos).

O servidor sempre verifica:

1. se o participante pertence à sala informada;
2. se a vaga está ativa;
3. se a operação é permitida no estado atual da partida.

## 4. Códigos de status

| Código | Significado |
|---|---|
| 200 | Sucesso |
| 400 | Requisição inválida |
| 401 | Não autenticado |
| 403 | Sem permissão |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex.: resposta duplicada) |
| 419 | Token CSRF inválido ou expirado |
| 422 | Erro de validação |
| 429 | Limite de tentativas excedido |
| 500 | Erro interno |
