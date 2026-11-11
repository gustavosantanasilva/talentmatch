-- Migração 001 — avatares dos participantes
-- Adiciona avatar (emoji) e cor de fundo à tabela de participantes.
-- Seguro para executar uma única vez em bancos já populados.

ALTER TABLE `participants`
    ADD COLUMN `avatar` VARCHAR(16) NOT NULL DEFAULT '🙂' AFTER `nickname`,
    ADD COLUMN `avatar_color` VARCHAR(7) NOT NULL DEFAULT '#087F5B' AFTER `avatar`;
