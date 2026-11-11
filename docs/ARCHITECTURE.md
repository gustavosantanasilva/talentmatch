# Arquitetura — TalentMatch

## 1. Visão geral

O TalentMatch é uma aplicação web **PHP + MySQL** executada em hospedagem
compartilhada. Não há servidor de aplicação dedicado, WebSocket próprio, processos
persistentes ou filas com workers. Toda a comunicação é feita por **requisições HTTP
orientadas a eventos**, com respostas **JSON padronizadas**.

```
┌──────────────────────────────────────────────────────────────────┐
│                          NAVEGADOR                                │
│  Painel do organizador   ·   Celulares dos participantes   ·   Telão
└───────────────┬──────────────────────┬───────────────────────────┘
                │  HTML inicial        │  fetch() + JSON
                ▼                      ▼
┌──────────────────────────────────────────────────────────────────┐
│                        public/  (raiz pública)                     │
│  index.php · entrar.php · perfil.php · vaga.php · telao.php        │
│  admin/*.php · api/**/*.php · assets/                             │
└───────────────┬──────────────────────────────────────────────────┘
                │  require app/bootstrap.php
                ▼
┌──────────────────────────────────────────────────────────────────┐
│                       app/  (código privado)                       │
│  core/  (Env, Config, Database, Response, Request, Session, ...)   │
│  services/  (DeepSeek, JobFallback, Matching)                      │
│  models/ · controllers/ · middleware/ · views/                     │
└───────────────┬──────────────────────────────────────────────────┘
                │  PDO + prepared statements
                ▼
┌──────────────────────────────────────────────────────────────────┐
│                     MySQL / MariaDB (InnoDB)                       │
└──────────────────────────────────────────────────────────────────┘
                ▲
                │  cURL HTTPS (chave somente no servidor)
        app/services/DeepSeekService ──▶ api.deepseek.com
```

## 2. Separação de responsabilidades

| Camada | Local | Responsabilidade |
|---|---|---|
| Apresentação | `public/`, `app/views/` | HTML, CSS e JavaScript; nenhuma regra de negócio |
| Entrada | `public/api/**` | Receber a requisição, validar, delegar e responder JSON |
| Núcleo | `app/core/` | Infraestrutura: configuração, banco, sessão, respostas |
| Serviços | `app/services/` | Regras de negócio: IA, fallback e cálculo de compatibilidade |
| Modelos | `app/models/` | Acesso a dados por tabela |
| Configuração | `app/config/` + `.env` | Parâmetros públicos e segredos privados |

**Princípio central:** o navegador nunca decide o estado do jogo, a identidade de um
participante ou uma pontuação. Isso é sempre responsabilidade do servidor.

## 3. Inicialização (`app/bootstrap.php`)

1. Define os caminhos (`BASE_PATH`, `APP_PATH`, `PUBLIC_PATH`, `STORAGE_PATH`).
2. Registra o autoloader **PSR-4**: `App\` → `app/` (subdiretórios em minúsculas).
3. Carrega os helpers globais.
4. Carrega o `.env` (`App\Core\Env`) e a configuração (`App\Core\Config`).
5. Configura timezone e o tratamento global de erros/exceções.
6. Inicia a sessão segura (exceto em CLI).

## 4. Núcleo (`app/core/`)

| Classe | Papel |
|---|---|
| `Env` | Lê variáveis de ambiente do `.env` |
| `Config` | Acesso à configuração por notação de ponto (`app.game.max_jobs`) |
| `Database` | Conexão PDO única, transações e verificação de conexão |
| `Response` | Respostas JSON padronizadas (sucesso, erro, validação) |
| `Request` | Leitura de dados de entrada (JSON ou formulário) |
| `Session` | Sessões seguras com cookies `HttpOnly` e `SameSite` |
| `Csrf` | Geração e verificação de token CSRF |
| `Validator` | Validação de dados no servidor |
| `View` | Renderização de templates com layout |
| `Logger` | Registro de eventos e erros em arquivo |

## 5. Estados da partida

O servidor controla todas as transições:

```
WAITING ─▶ PROFILES ─▶ GENERATING ─▶ JOBS ─▶ RESULTS ─▶ FINISHED
```

- `WAITING`: sala criada, participantes entrando.
- `PROFILES`: participantes preenchem o perfil.
- `GENERATING`: IA (ou fallback) gerando as vagas.
- `JOBS`: apresentação e candidaturas.
- `RESULTS`: cálculo e apresentação dos rankings.
- `FINISHED`: partida encerrada.

Nenhuma transição é aceita a partir de dados enviados pelo JavaScript sem validação
de permissão no servidor.

## 6. Estratégia de sincronização

Para respeitar os limites da hospedagem compartilhada:

- **Eventos explícitos** disparam operações HTTP (entrar, finalizar perfil, iniciar,
  avançar vaga, candidatar-se, encerrar, publicar resultados).
- As telas **consultam o estado sob demanda** (`GET /api/game/state.php`), sem polling
  agressivo.
- Existe **atualização manual** (botão "Atualizar") como contingência garantida.
- Se um serviço de tempo real externo for integrado no futuro, ele será opcional e
  jamais um requisito para o funcionamento.

## 7. Design system

Identidade visual própria, definida em `public/assets/css/app.css`:

- Paleta: verde-esmeralda (`#087F5B`), azul-petróleo (`#164E63`), grafite (`#17212B`),
  dourado de destaque (`#C69B52`) e tons claros.
- Tipografia: **Manrope** (títulos) e **Inter** (texto).
- Componentes reutilizáveis: botões, cards, badges, inputs, progresso, tabelas,
  rankings, modais, toasts e banners.
- Mobile-first, com atenção a contraste, espaçamento e hierarquia.

Bootstrap 5, Chart.js e QRCode.js são **vendorizados** em `public/assets/vendor/` para
funcionar mesmo sem CDN durante a apresentação.
