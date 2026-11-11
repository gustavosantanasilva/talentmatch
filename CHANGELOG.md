# Changelog

Todas as mudanças relevantes do TalentMatch são registradas aqui.
O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/) e o
projeto adota [Versionamento Semântico](https://semver.org/lang/pt-BR/).

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
