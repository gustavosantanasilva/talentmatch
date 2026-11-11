# Changelog

Todas as mudanças relevantes do TalentMatch são registradas aqui.
O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/) e o
projeto adota [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [0.2.0] — 2026-11-11

### Adicionado

- Autenticação de administrador baseada em sessão, com CSRF e limitação de
  tentativas (5 tentativas falhas bloqueiam o acesso por 300 segundos).
- Middleware `Auth` com variantes para páginas (`requirePage`) e API (`requireApi`).
- Painel administrativo: dashboard com estatísticas e listagem de salas.
- Criação de salas (formulário no dashboard e endpoint de API compartilhando
  `RoomService::createRoom()`).
- Códigos de sala de 6 caracteres com alfabeto sem ambiguidades
  (`ABCDEFGHJKMNPQRSTUVWXYZ23456789`).
- Página de detalhe da sala com QR Code, URL de entrada e botão de cópia.
- Controle de estado da sala (`WAITING` → `PROFILES`, `FINISHED`) com registro
  de eventos de jogo.
- Modelos `Admin`, `Room` e `GameEvent`.
- Serviço `RoomService` (geração de código, criação, URL de entrada, transição
  de estado e payload de estado).
- Endpoints de API `POST /api/room/create.php` e `GET /api/room/state.php`.
- Layouts de administrador e de autenticação, com estilos próprios do painel.
- Casos de teste de salas (`tests/cases/04_rooms.php`) e teste de fumaça HTTP
  (`tests/smoke.php`).

### Notas

- O QR Code aponta para `entrar.php?room=CÓDIGO`, que será implementado na
  Fase 3 (participantes). Até lá a rota retorna 404.

## [0.1.0] — 2026-11-11

### Adicionado

- Estrutura modular de diretórios (frontend, backend, serviços, banco e docs).
- Bootstrap próprio com autoloader PSR-4, carregamento de `.env` e configuração.
- Núcleo: `Env`, `Config`, `Database` (PDO), `Response`, `Request`, `Session`,
  `Csrf`, `Validator`, `View` e `Logger`.
- Tratamento global de erros sem exposição de detalhes internos.
- Esquema completo do banco de dados (13 tabelas, InnoDB, utf8mb4, FKs e índices).
- Catálogo com 14 vagas fictícias para o fallback da IA.
- Design system próprio (verde-esmeralda, azul-petróleo e grafite) e página inicial
  responsiva com diagnóstico do sistema.
- Utilitários de frontend (fetch com CSRF, toasts e modal de confirmação).
- Suíte de testes em linha de comando (sintaxe, ambiente, banco e helpers).
- Documentação: README, arquitetura, instalação, banco, publicação, API, segurança,
  experimento e fluxo de Git.

### Notas

- As fases 2 a 9 ainda não foram implementadas. Consulte a seção
  "Status do desenvolvimento" no README.
