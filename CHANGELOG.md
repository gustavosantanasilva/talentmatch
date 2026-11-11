# Changelog

Todas as mudanças relevantes do TalentMatch são registradas aqui.
O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/) e o
projeto adota [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [0.4.0] — 2026-11-11

### Adicionado

- Montagem do perfil profissional do participante (`public/perfil.php`), disponível
  apenas durante a etapa `PROFILES` da partida.
- Formulário com área profissional, resumo, habilidades (nível básico/intermediário/
  avançado), experiências e formações, com linhas dinâmicas adicionadas e removidas no
  cliente.
- Validação completa no servidor: área permitida, limites de 20 habilidades, 10
  experiências e 10 formações, tamanhos de texto e rejeição de habilidades repetidas.
- Salvamento de rascunho e finalização do perfil; ao finalizar, o participante passa a
  `READY` e `profiles.is_complete` é marcado.
- Modelos `Profile`, `Skill`, `Experience` e `Education`, e serviço `ProfileService`
  (normalização, validação, persistência transacional e dados para a view).
- Endpoints `POST /api/profile/save.php` e `POST /api/profile/complete.php`.
- View de perfil do participante com formulário e resumo somente leitura quando a etapa
  já foi encerrada.
- Contador de **perfis concluídos** no painel administrativo e rótulos legíveis de status
  do participante (`Participant::statusLabel()`).
- Botão de acesso ao perfil na sala de espera quando a partida entra em `PROFILES`.
- Caso de teste `tests/cases/06_profiles.php` e novas verificações no teste de fumaça
  HTTP (salvar, finalizar, validação e bloqueio fora da etapa).

### Alterado

- A sala de espera do participante agora direciona para a montagem do perfil na etapa
  correta, em vez de apenas aguardar.

### Notas

- A geração de vagas (Fase 5) ainda não está disponível; após finalizar o perfil o
  participante aguarda o organizador.

## [0.3.0] — 2026-11-11

### Adicionado

- Entrada de participantes por QR Code (`public/entrar.php?room=CÓDIGO`), com apelido
  único por sala.
- Token de participante aleatório (32 bytes) armazenado **apenas como hash SHA-256**.
- Sala de espera do participante (`public/sala.php`) com lista de participantes,
  mensagens por estado da partida e atualização automática de baixa frequência.
- Middleware `ParticipantAuth`: sessão para navegação e cabeçalho
  `X-Participant-Token` para chamadas de API.
- Modelo `Participant` e serviço `ParticipantService` (entrada na sala, validação de
  apelido, lista pública e token).
- Endpoints `POST /api/room/join.php` e `GET /api/room/participants.php`.
- Layout do participante e estilos da área do participante (lista de presença).
- Caso de teste `tests/cases/05_participants.php` e novas verificações no teste de
  fumaça HTTP (entrada, apelido duplicado, token, API e sala encerrada).
- O QR Code criado na Fase 2 agora leva a uma página funcional.

### Notas

- A montagem de perfis (Fase 4) ainda não está disponível; a sala de espera apenas
  acompanha o estado da partida.

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
