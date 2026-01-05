-- 003_room_tokens.sql
-- Adds stateful room tokens (token-based access) to rooms.
--
-- Purpose:
-- - Issue short-lived access tokens for a room (host/moderator/guest)
-- - Tokens are stateful (stored in DB)
-- - Store only SHA-256 hash of token (token itself is never stored)
-- - Prepare engine for access control + integrations (WP/SocialCore/DatingCore)
--
-- Effect:
-- - New table: room_tokens
-- - No foreign keys in v0.1 (keep it simple)
--
-- Checks after run:
-- 1) SHOW TABLES LIKE 'room_tokens';
-- 2) SHOW COLUMNS FROM room_tokens;
-- 3) SHOW INDEX FROM room_tokens;
-- 4) SELECT COUNT(*) FROM room_tokens;

CREATE TABLE IF NOT EXISTS room_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_hash CHAR(64) NOT NULL,
  room_id VARCHAR(64) NOT NULL,
  role VARCHAR(16) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  revoked_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_room_tokens_token_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_room_tokens_room_id ON room_tokens (room_id);
CREATE INDEX idx_room_tokens_expires_at ON room_tokens (expires_at);
