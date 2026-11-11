# Banco de dados — TalentMatch

Todos os scripts estão em `database/`. O esquema usa **InnoDB**, **utf8mb4** e
integridade referencial com chaves estrangeiras e índices.

## 1. Diagrama de relacionamentos

```
ADMINS
  └── ROOMS (salas)
        ├── PARTICIPANTS
        │     ├── PROFILES
        │     │     ├── SKILLS
        │     │     ├── EXPERIENCES
        │     │     └── EDUCATIONS
        │     └── APPLICATIONS
        ├── JOBS (vagas)
        │     ├── JOB_SKILLS
        │     └── MATCH_RESULTS
        └── GAME_EVENTS

JOB_CATALOG (catálogo de fallback, independente das salas)
```

## 2. Tabelas

### `admins`
Administradores do painel.

| Coluna | Tipo | Observação |
|---|---|---|
| `id` | INT UNSIGNED PK | |
| `username` | VARCHAR(50) UNIQUE | |
| `password_hash` | VARCHAR(255) | bcrypt |
| `display_name` | VARCHAR(100) | |
| `last_login_at` | DATETIME | |

### `rooms`
Salas/partidas.

| Coluna | Tipo | Observação |
|---|---|---|
| `code` | CHAR(6) UNIQUE | Código exibido no telão |
| `status` | ENUM | `WAITING`,`PROFILES`,`GENERATING`,`JOBS`,`RESULTS`,`FINISHED` |
| `current_job_index` | TINYINT | Índice da vaga atual (0-based) |
| `jobs_source` | ENUM | `NONE`,`AI`,`FALLBACK` |
| `admin_id` | FK → admins | `ON DELETE SET NULL` |

### `participants`
Participantes de uma sala. O token de sessão é armazenado **apenas como hash SHA-256**.

| Coluna | Tipo | Observação |
|---|---|---|
| `room_id` | FK → rooms | |
| `nickname` | VARCHAR(30) | único por sala |
| `token_hash` | CHAR(64) UNIQUE | `hash('sha256', $token)` |
| `status` | ENUM | `WAITING`,`PROFILE_PENDING`,`READY` |
| `is_active` | TINYINT | permite remover sem apagar histórico |

### `profiles`
Perfil profissional (1:1 com participante).

| Coluna | Tipo |
|---|---|
| `participant_id` | FK UNIQUE → participants |
| `area` | VARCHAR(60) |
| `headline` | VARCHAR(120) |
| `is_complete` | TINYINT |
| `completed_at` | DATETIME |

### `skills`
| Coluna | Tipo |
|---|---|
| `profile_id` | FK → profiles |
| `name` | VARCHAR(60) |
| `name_norm` | VARCHAR(60) (normalizado para comparação) |
| `level` | ENUM `BASIC`,`INTERMEDIATE`,`ADVANCED` |

### `experiences`
`title`, `description`, `competencies` (FK `profile_id`).

### `educations`
`course`, `institution`, `description` (FK `profile_id`).

### `job_catalog`
Catálogo de vagas fictícias para o **fallback** quando a IA está indisponível.
Campos de lista (`benefits`, `activities`, `required_skills`, `desirable_skills`) são
armazenados como **JSON**.

### `jobs`
Vagas geradas para uma sala (no máximo **4**). Campos escalares + `benefits`/`activities`
em JSON. Restrição `UNIQUE (room_id, position)` garante no máximo quatro posições por
sala.

### `job_skills`
Requisitos das vagas: `name`, `name_norm`, `type` (`REQUIRED`/`DESIRABLE`). Fonte de
verdade para o cálculo de compatibilidade (evita duplicação com `jobs`).

### `applications`
Respostas por vaga. `status` é `APPLIED` (candidatou-se) ou `SKIPPED` (pulou).
**Não existe** o estado `SAVED`. O índice `UNIQUE (participant_id, job_id)` impede
respostas duplicadas.

### `match_results`
Notas calculadas em PHP.

| Coluna | Descrição |
|---|---|
| `required_score` | Nota de habilidades obrigatórias (0–100) |
| `desirable_score` | Nota de habilidades desejáveis (0–100) |
| `experience_score` | Nota de experiências (0–100) |
| `education_score` | Nota de formação (0–100) |
| `final_score` | Nota final ponderada (0–100, 2 casas decimais) |
| `rank_position` | Posição no ranking da vaga |
| `breakdown` | Detalhamento em JSON |

`UNIQUE (job_id, participant_id)` impede resultados duplicados.

### `game_events`
Log de eventos da partida para sincronização e depuração (`type`, `payload` JSON).

## 3. Regras de integridade

- Todas as tabelas usam **InnoDB** (transações e FKs).
- Chaves estrangeiras com `ON DELETE CASCADE` (ou `SET NULL` quando apropriado).
- Índices em todas as colunas de junção e filtro frequentes.
- Candidaturas e resultados protegidos por índices únicos.
- Operações críticas (geração de vagas, candidatura, cálculo) devem usar transações.

## 4. Dados iniciais

- **1 administrador**: `admin` / `talentmatch` (hash bcrypt). Altere após o primeiro acesso.
- **14 vagas fictícias** no `job_catalog`, cobrindo 8 áreas profissionais.

## 5. Manutenção

```bash
# Reimportar do zero (apaga os dados)
mysql -u USUARIO -p BANCO < database/schema.sql
mysql -u USUARIO -p BANCO < database/seed_jobs.sql

# Backup
mysqldump -u USUARIO -p BANCO > backup.sql
```
