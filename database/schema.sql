-- ===========================================================================
-- TalentMatch — Esquema do banco de dados (MySQL / MariaDB)
-- ===========================================================================
-- Projeto educacional. Todas as empresas, vagas e salários são fictícios.
--
-- Instalação:
--   mysql -u USUARIO -p NOME_BANCO < database/schema.sql
--   mysql -u USUARIO -p NOME_BANCO < database/seed_jobs.sql
--
-- Compatível com MySQL 5.7+ e MariaDB 10.2+ (InnoDB, utf8mb4).
-- ===========================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `game_events`;
DROP TABLE IF EXISTS `match_results`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `job_skills`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_catalog`;
DROP TABLE IF EXISTS `educations`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `participants`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `admins`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- Administradores
-- ---------------------------------------------------------------------------
CREATE TABLE `admins` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(50)  NOT NULL,
    `password_hash`  VARCHAR(255) NOT NULL,
    `display_name`   VARCHAR(100) NOT NULL DEFAULT 'Administrador',
    `last_login_at`  DATETIME     NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Salas (partidas)
-- ---------------------------------------------------------------------------
CREATE TABLE `rooms` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`              CHAR(6)      NOT NULL,
    `status`            ENUM('WAITING','PROFILES','GENERATING','JOBS','RESULTS','FINISHED')
                                     NOT NULL DEFAULT 'WAITING',
    `current_job_index` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `jobs_source`       ENUM('NONE','AI','FALLBACK') NOT NULL DEFAULT 'NONE',
    `admin_id`          INT UNSIGNED NULL,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `started_at`        DATETIME     NULL,
    `finished_at`       DATETIME     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rooms_code` (`code`),
    KEY `idx_rooms_status` (`status`),
    KEY `idx_rooms_admin` (`admin_id`),
    CONSTRAINT `fk_rooms_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Participantes
-- O token nunca é armazenado em texto puro: apenas o hash SHA-256.
-- ---------------------------------------------------------------------------
CREATE TABLE `participants` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`     INT UNSIGNED NOT NULL,
    `nickname`    VARCHAR(30)  NOT NULL,
    `avatar`      VARCHAR(16)  NOT NULL DEFAULT '🙂',
    `avatar_color` VARCHAR(7) NOT NULL DEFAULT '#087F5B',
    `token_hash`  CHAR(64)     NOT NULL,
    `status`      ENUM('WAITING','PROFILE_PENDING','READY') NOT NULL DEFAULT 'WAITING',
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `joined_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_seen`   DATETIME     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_participants_nickname` (`room_id`, `nickname`),
    UNIQUE KEY `uq_participants_token` (`token_hash`),
    KEY `idx_participants_room` (`room_id`),
    CONSTRAINT `fk_participants_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Perfis profissionais (1:1 com participante)
-- ---------------------------------------------------------------------------
CREATE TABLE `profiles` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `participant_id` INT UNSIGNED NOT NULL,
    `area`           VARCHAR(60)  NOT NULL,
    `headline`       VARCHAR(120) NULL,
    `is_complete`    TINYINT(1)   NOT NULL DEFAULT 0,
    `completed_at`   DATETIME     NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_profiles_participant` (`participant_id`),
    KEY `idx_profiles_area` (`area`),
    CONSTRAINT `fk_profiles_participant` FOREIGN KEY (`participant_id`)
        REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Habilidades
-- ---------------------------------------------------------------------------
CREATE TABLE `skills` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `profile_id` INT UNSIGNED NOT NULL,
    `name`       VARCHAR(60)  NOT NULL,
    `name_norm`  VARCHAR(60)  NOT NULL,
    `level`      ENUM('BASIC','INTERMEDIATE','ADVANCED') NOT NULL DEFAULT 'BASIC',
    PRIMARY KEY (`id`),
    KEY `idx_skills_profile` (`profile_id`),
    KEY `idx_skills_name_norm` (`name_norm`),
    CONSTRAINT `fk_skills_profile` FOREIGN KEY (`profile_id`)
        REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Experiências
-- ---------------------------------------------------------------------------
CREATE TABLE `experiences` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `profile_id`   INT UNSIGNED NOT NULL,
    `title`        VARCHAR(120) NOT NULL,
    `description`  TEXT         NULL,
    `competencies` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    KEY `idx_experiences_profile` (`profile_id`),
    CONSTRAINT `fk_experiences_profile` FOREIGN KEY (`profile_id`)
        REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Formações
-- ---------------------------------------------------------------------------
CREATE TABLE `educations` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `profile_id`   INT UNSIGNED NOT NULL,
    `course`       VARCHAR(120) NOT NULL,
    `institution`  VARCHAR(120) NOT NULL,
    `description`  VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    KEY `idx_educations_profile` (`profile_id`),
    CONSTRAINT `fk_educations_profile` FOREIGN KEY (`profile_id`)
        REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Catálogo de vagas fictícias (usado pelo fallback quando a IA está indisponível)
-- ---------------------------------------------------------------------------
CREATE TABLE `job_catalog` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `area`              VARCHAR(60)  NOT NULL,
    `job_title`         VARCHAR(120) NOT NULL,
    `company_name`      VARCHAR(120) NOT NULL,
    `company_sector`    VARCHAR(80)  NOT NULL,
    `opportunity_type`  VARCHAR(60)  NOT NULL,
    `salary_range`      VARCHAR(60)  NOT NULL,
    `weekly_hours`      VARCHAR(40)  NOT NULL,
    `work_mode`         VARCHAR(40)  NOT NULL,
    `contract_type`     VARCHAR(40)  NOT NULL,
    `benefits`          JSON         NULL,
    `activities`        JSON         NULL,
    `required_skills`   JSON         NULL,
    `desirable_skills`  JSON         NULL,
    `related_experience` TEXT        NULL,
    `relevant_education` TEXT        NULL,
    `description`       TEXT         NULL,
    `fictional_notice`  VARCHAR(120) NOT NULL DEFAULT 'VAGA FICTÍCIA — SIMULAÇÃO EDUCACIONAL',
    `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_catalog_area` (`area`),
    KEY `idx_catalog_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Vagas da partida (até 4 por sala)
-- ---------------------------------------------------------------------------
CREATE TABLE `jobs` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`            INT UNSIGNED NOT NULL,
    `position`           TINYINT UNSIGNED NOT NULL,
    `source`             ENUM('AI','FALLBACK') NOT NULL DEFAULT 'FALLBACK',
    `company_name`       VARCHAR(120) NOT NULL,
    `company_sector`     VARCHAR(80)  NOT NULL,
    `job_title`          VARCHAR(120) NOT NULL,
    `opportunity_type`   VARCHAR(60)  NOT NULL,
    `salary_range`       VARCHAR(60)  NOT NULL,
    `weekly_hours`       VARCHAR(40)  NOT NULL,
    `work_mode`          VARCHAR(40)  NOT NULL,
    `contract_type`      VARCHAR(40)  NOT NULL,
    `benefits`           JSON         NULL,
    `activities`         JSON         NULL,
    `related_experience` TEXT         NULL,
    `relevant_education` TEXT         NULL,
    `description`        TEXT         NULL,
    `fictional_notice`   VARCHAR(120) NOT NULL DEFAULT 'VAGA FICTÍCIA — SIMULAÇÃO EDUCACIONAL',
    `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_jobs_room_position` (`room_id`, `position`),
    KEY `idx_jobs_room` (`room_id`),
    CONSTRAINT `fk_jobs_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Requisitos das vagas (habilidades obrigatórias e desejáveis)
-- ---------------------------------------------------------------------------
CREATE TABLE `job_skills` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `job_id`     INT UNSIGNED NOT NULL,
    `name`       VARCHAR(60)  NOT NULL,
    `name_norm`  VARCHAR(60)  NOT NULL,
    `type`       ENUM('REQUIRED','DESIRABLE') NOT NULL DEFAULT 'REQUIRED',
    PRIMARY KEY (`id`),
    KEY `idx_job_skills_job` (`job_id`),
    KEY `idx_job_skills_type` (`job_id`, `type`),
    CONSTRAINT `fk_job_skills_job` FOREIGN KEY (`job_id`)
        REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Candidaturas (APPLIED = candidatou-se / SKIPPED = pulou a vaga)
-- O índice único impede respostas duplicadas por vaga.
-- ---------------------------------------------------------------------------
CREATE TABLE `applications` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`        INT UNSIGNED NOT NULL,
    `participant_id` INT UNSIGNED NOT NULL,
    `job_id`         INT UNSIGNED NOT NULL,
    `status`         ENUM('APPLIED','SKIPPED') NOT NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_applications_participant_job` (`participant_id`, `job_id`),
    KEY `idx_applications_job_status` (`job_id`, `status`),
    KEY `idx_applications_room` (`room_id`),
    CONSTRAINT `fk_applications_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_applications_participant` FOREIGN KEY (`participant_id`)
        REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_applications_job` FOREIGN KEY (`job_id`)
        REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Resultados de compatibilidade (calculados em PHP)
-- ---------------------------------------------------------------------------
CREATE TABLE `match_results` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`           INT UNSIGNED NOT NULL,
    `job_id`            INT UNSIGNED NOT NULL,
    `participant_id`    INT UNSIGNED NOT NULL,
    `required_score`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `desirable_score`   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `experience_score`  DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `education_score`   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `final_score`       DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `rank_position`     SMALLINT UNSIGNED NULL,
    `breakdown`         JSON         NULL,
    `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_results_job_participant` (`job_id`, `participant_id`),
    KEY `idx_results_room` (`room_id`),
    KEY `idx_results_job_score` (`job_id`, `final_score`),
    KEY `idx_results_participant` (`participant_id`),
    CONSTRAINT `fk_results_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_results_job` FOREIGN KEY (`job_id`)
        REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_results_participant` FOREIGN KEY (`participant_id`)
        REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Eventos da partida (log para sincronização e depuração)
-- ---------------------------------------------------------------------------
CREATE TABLE `game_events` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id`        INT UNSIGNED NOT NULL,
    `participant_id` INT UNSIGNED NULL,
    `type`           VARCHAR(40)  NOT NULL,
    `payload`        JSON         NULL,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_events_room` (`room_id`, `id`),
    KEY `idx_events_type` (`room_id`, `type`),
    CONSTRAINT `fk_events_room` FOREIGN KEY (`room_id`)
        REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_events_participant` FOREIGN KEY (`participant_id`)
        REFERENCES `participants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================================
-- Dados iniciais de demonstração
-- ===========================================================================
-- Administrador padrão:
--   usuário: admin
--   senha:   talentmatch      <-- ALTERE APÓS O PRIMEIRO ACESSO
-- (hash bcrypt abaixo; o sistema aceita apenas o hash, nunca a senha em texto)
INSERT INTO `admins` (`username`, `password_hash`, `display_name`) VALUES
('admin', '$2y$12$DN7aLtA0zoJC.6OUuPmyJeqy3XCGfiiI6mbqmJIaE8v0wAgPPeEfO', 'Administrador TalentMatch');
