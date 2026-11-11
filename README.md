<div align="center">

# TalentMatch

**Plataforma multiplayer de recrutamento simulado com inteligência artificial, análise de compatibilidade profissional e demonstração de vieses algorítmicos.**

_Conectando talentos, oportunidades e inteligência artificial._

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-8.0%20%7C%2010.2%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Status](https://img.shields.io/badge/status-em%20desenvolvimento-C69B52?style=for-the-badge)

</div>

---

## Sobre o projeto

O **TalentMatch** é um jogo multiplayer de simulação de recrutamento criado para uma
feira de ciências e tecnologia. Vários participantes acessam uma sala por **QR Code**,
criam um perfil profissional, recebem **vagas fictícias geradas por IA** e decidem se
desejam se candidatar. Em seguida, o sistema calcula a **compatibilidade entre
candidatos e vagas** e apresenta rankings — abrindo espaço para discutir
**estereótipos, transparência e vieses algorítmicos**.

> **Aviso importante:** todas as empresas, vagas, salários e benefícios são
> **fictícios**. O TalentMatch é um projeto **educacional** e **não** é uma plataforma
> real de contratação. Nenhum dado sensível é coletado e nenhum currículo real é
> enviado para a IA.

### O que o projeto demonstra

- Uma aplicação prática e real de IA generativa (DeepSeek) com validação no servidor.
- Um jogo multiplayer síncrono sobre HTTP, compatível com hospedagem compartilhada.
- Um algoritmo de compatibilidade **explicável**, com pesos publicados.
- Um experimento sobre **vieses**: características pessoais irrelevantes não podem
  alterar uma avaliação profissional.

---

## Status do desenvolvimento

O projeto é construído em fases. Esta tabela reflete **o que já funciona de verdade**
(sem protótipos ou dados simulados disfarçados de funcionalidade).

| Fase | Escopo | Status |
|:----:|--------|:------:|
| 1 | Estrutura, configuração privada, conexão MySQL, respostas JSON, scripts SQL, layout inicial | ✅ Concluída |
| 2 | Administração: login, dashboard, criação de sala, QR Code, controle de estado | ✅ Concluída |
| 3 | Participantes: entrada por QR Code, apelido, token, sala de espera | ✅ Concluída |
| 4 | Perfis: habilidades, experiências, formações, validação e finalização | ✅ Concluída |
| 5 | Vagas: `DeepSeekService`, prompt, validação e fallback offline | ⏳ Planejada |
| 6 | Candidaturas: candidatar-se, pular, anti-duplicidade | ⏳ Planejada |
| 7 | Compatibilidade: `MatchingService`, pesos, dicionário de sinônimos, rankings | ⏳ Planejada |
| 8 | Resultados: gráficos, telão, resultado individual, experimento de vieses | ⏳ Planejada |
| 9 | Testes finais e publicação na InfinityFree | ⏳ Planejada |

O que existe hoje, comprovado por testes automatizados:

- Estrutura modular de diretórios (frontend, backend, serviços, banco e documentação).
- Bootstrap próprio com autoloader PSR-4, carregamento de `.env` e configuração.
- Conexão PDO com MySQL/MariaDB (prepared statements, InnoDB, utf8mb4).
- Camada de respostas JSON padronizadas e tratamento global de erros.
- Sessões seguras, proteção CSRF e validação de dados no servidor.
- Esquema completo do banco (13 tabelas) + catálogo com 14 vagas fictícias de fallback.
- Design system próprio e página inicial responsiva.
- **Painel do organizador**: login com limite de tentativas, dashboard com estatísticas,
  criação de salas com código único, QR Code e URL de entrada, e controle de estado com
  transições validadas no servidor.
- **Participantes**: entrada por QR Code (`entrar.php?room=CÓDIGO`), apelido único por
  sala, token aleatório guardado apenas como hash SHA-256 e **sala de espera** com lista
  de participantes e atualização automática do estado.
- **Perfis profissionais**: montagem de perfil em `perfil.php` (área, resumo, até 20
  habilidades, 10 experiências e 10 formações) com linhas dinâmicas, validação no
  servidor, salvamento de rascunho e finalização. A edição só é liberada durante a etapa
  `PROFILES`; ao finalizar, o participante passa a `READY` e o painel administrativo
  acompanha quantos perfis já foram concluídos.
- **Interface de jogo (estilo Kahoot)**: nova camada visual (`game.css` + `game.js`) com
  botões grandes, cartões arredondados, cores de resposta e animações de entrada.
- **Avatares**: cada participante escolhe um **emoji** e uma **cor de fundo** ao entrar na
  sala, e pode trocar depois pela sala de espera. O avatar aparece no lobby, na sala de
  espera e no telão.
- **Lobby do organizador**: tela da sala com **QR Code grande à esquerda** e **lista de
  participantes ao vivo à direita**, atualizada sozinha a cada 3 segundos (com animação
  nos jogadores que acabaram de entrar).
- **Modo telão** (`admin/telao.php`): tela cheia para projetar o QR Code e a lista de
  jogadores, sem o chrome do painel.
- **API interna**: `POST /api/room/create.php`, `GET /api/room/state.php`,
  `POST /api/room/join.php`, `GET /api/room/participants.php`,
  `POST /api/profile/save.php`, `POST /api/profile/complete.php` e
  `POST /api/participant/identity.php`, com respostas JSON padronizadas.
- Suíte de testes (`tests/`) com verificação de sintaxe, ambiente, banco, helpers, regras
  de sala, regras de participantes, regras de perfil, regras de avatar e um teste de
  fumaça HTTP completo.

---

## Tecnologias

| Componente | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3, JavaScript ES6+ |
| Framework de UI | Bootstrap 5 (vendorizado) |
| Backend | PHP 8.1+ (sem frameworks) |
| Banco de dados | MySQL 8 / MariaDB 10.2+ (InnoDB, utf8mb4) |
| Comunicação | Fetch API (JSON) |
| Inteligência artificial | DeepSeek API via PHP cURL |
| Gráficos | Chart.js (vendorizado) |
| QR Code | QRCode.js (vendorizado) |
| Hospedagem | InfinityFree (hospedagem compartilhada) |

> Sem Node.js em produção, sem Python, sem WebSocket próprio e sem serviços externos
> obrigatórios. A sincronização acontece por **requisições HTTP orientadas a eventos**,
> com estratégia de contingência (atualização manual) para não depender de polling
> agressivo na hospedagem compartilhada.

---

## Arquitetura

```
Navegador (participante / admin / telão)
        │  fetch + JSON
        ▼
public/  ← raiz pública (apenas arquivos acessíveis)
        │
        ▼
app/     ← núcleo, serviços, modelos, views (fora da raiz pública)
        │  PDO + prepared statements
        ▼
MySQL / MariaDB

        app/services/DeepSeekService ──cURL──▶ api.deepseek.com
```

- **public/** concentra apenas entrypoints e assets.
- **app/** guarda o núcleo, serviços e templates — nunca exposto diretamente.
- **database/** contém os scripts SQL; **storage/** guarda logs e cache.
- Segredos ficam no arquivo `.env`, fora da raiz pública.

Detalhes completos em [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

---

## Estrutura de diretórios

```text
talentmatch/
├── public/                     # Raiz pública do site
│   ├── index.php               # Página inicial
│   ├── entrar.php              # Entrada do participante por QR Code (Fase 3)
│   ├── sala.php                # Sala de espera do participante (Fase 3)
│   ├── perfil.php              # Perfil profissional do participante (Fase 4)
│   ├── assets/                 # CSS, JS, imagens e bibliotecas vendorizadas
│   │   ├── css/app.css         # Design system base
│   │   ├── css/game.css        # Game Kit — interface estilo Kahoot (v0.5)
│   │   ├── js/app.js           # Utilitários (fetch, toasts, modal)
│   │   └── js/game.js          # Avatares, grade de jogadores e sala ao vivo
│   │   └── vendor/             # Bootstrap, Chart.js, QRCode.js
│   ├── admin/                  # Painel do organizador (Fases 2–5)
│   │   ├── dashboard.php       # Visão geral e criação de salas
│   │   ├── sala.php            # Lobby: QR Code + jogadores ao vivo
│   │   └── telao.php           # Modo telão em tela cheia
│   └── api/                    # Endpoints JSON (Fases 2–8)
│       ├── room/               # Criação, estado, entrada e participantes
│       ├── profile/            # Salvamento e finalização do perfil
│       └── participant/        # Identidade (apelido e avatar)
│
├── app/                        # Código privado da aplicação
│   ├── bootstrap.php           # Inicialização, autoloader, erros, sessão
│   ├── config/                 # Configuração (lê segredos do .env)
│   ├── core/                   # Env, Config, Database, Response, Request, ...
│   ├── controllers/            # Controladores (Fases 2–8)
│   ├── models/                 # Modelos de dados (Fases 2–8)
│   ├── services/               # DeepSeekService, JobFallbackService, MatchingService
│   ├── middleware/             # Autenticação e permissões
│   ├── helpers/                # Funções utilitárias
│   └── views/                  # Templates (layouts e páginas)
│
├── database/
│   ├── schema.sql              # Estrutura completa + admin inicial
│   ├── seed_jobs.sql           # Catálogo de vagas de fallback
│   └── migrations/             # Migrações incrementais do banco
│
├── docs/                       # Documentação
├── tests/                      # Suíte de testes (linha de comando)
├── storage/                    # Logs e cache (fora da raiz pública)
├── .env.example                # Modelo de variáveis de ambiente
└── README.md
```

---

## Instalação rápida (desenvolvimento)

Requisitos: **PHP 8.1+** (com `pdo_mysql`, `curl`, `mbstring`, `json`) e **MySQL 8**
ou **MariaDB 10.2+**.

```bash
# 1. Clone o repositório
git clone <URL_DO_REPOSITORIO> talentmatch
cd talentmatch

# 2. Crie o arquivo de ambiente e preencha as credenciais
cp .env.example .env
#    edite .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# 3. Importe o banco de dados
mysql -u USUARIO -p NOME_BANCO < database/schema.sql
mysql -u USUARIO -p NOME_BANCO < database/seed_jobs.sql

# 4. Suba o servidor de desenvolvimento
php -S localhost:8000 -t public
```

Acesse `http://localhost:8000`. A página inicial exibe o **diagnóstico do sistema**,
confirmando se a conexão com o banco está ativa.

Credenciais iniciais do painel administrativo:

```text
usuário: admin
senha:   talentmatch      ← altere após o primeiro acesso
```

> O arquivo `.env` **nunca** deve ser versionado. Ele já está no `.gitignore`.

Passo a passo detalhado em [`docs/INSTALLATION.md`](docs/INSTALLATION.md) e publicação
em [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## Testes

A suíte de testes roda por linha de comando, sem dependências externas:

```bash
php tests/lint.php     # Verifica a sintaxe de todos os arquivos PHP
php tests/run.php      # Executa a suíte de testes
php tests/run.php --only=database   # Executa apenas um grupo

# Teste de fumaça HTTP (requer o servidor em execução)
php -S 127.0.0.1:8080 -t public &
php tests/smoke.php http://127.0.0.1:8080
```

Os testes de banco são executados automaticamente quando o `.env` está configurado;
caso contrário, são ignorados com um aviso.

---

## Segurança e privacidade

- PDO com **prepared statements** (emulação desativada) contra SQL Injection.
- Saída escapada contra **XSS** e **token CSRF** em formulários e requisições.
- **Validação sempre no servidor**; o cliente nunca define estado ou identidade.
- Token do participante gerado aleatoriamente e armazenado **apenas como hash**.
- Chave da DeepSeek **exclusivamente no servidor**, carregada do `.env`.
- Sem CPF, endereço, telefone ou dados sensíveis. Apenas **apelidos** e dados
  profissionais.
- Respostas de erro sem *stack traces* nem informações internas.

Detalhes em [`docs/SECURITY.md`](docs/SECURITY.md).

---

## Documentação

| Documento | Conteúdo |
|---|---|
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Visão geral da arquitetura e fluxo |
| [`docs/INSTALLATION.md`](docs/INSTALLATION.md) | Instalação local passo a passo |
| [`docs/DATABASE.md`](docs/DATABASE.md) | Modelo de dados e relacionamentos |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | Publicação na InfinityFree |
| [`docs/API.md`](docs/API.md) | Endpoints internos e formato JSON |
| [`docs/SECURITY.md`](docs/SECURITY.md) | Segurança e privacidade |
| [`docs/EXPERIMENT.md`](docs/EXPERIMENT.md) | Experimento sobre vieses algorítmicos |
| [`docs/GIT_WORKFLOW.md`](docs/GIT_WORKFLOW.md) | Convenções de commits e versionamento |

---

## Contribuição

Sugestões e correções são bem-vindas. Leia
[`CONTRIBUTING.md`](CONTRIBUTING.md) antes de abrir uma *issue* ou *pull request*.

---

## Licença

Distribuído sob a licença **MIT**. Consulte [`LICENSE`](LICENSE).

<div align="center">

Feito com PHP, curiosidade e muita conversa sobre algoritmos justos.

</div>
