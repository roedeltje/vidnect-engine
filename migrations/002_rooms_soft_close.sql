-- 002_rooms_soft_close.sql
-- Adds soft-close lifecycle support to rooms.
--
-- Purpose:
-- - Introduce a room lifecycle without deleting rooms
-- - Prepare engine for signaling & access control
--
-- Effect:
-- - Existing rooms remain OPEN
-- - New rooms default to OPEN
--
-- Checks after run:
-- 1) SHOW COLUMNS FROM rooms LIKE 'status';
-- 2) SHOW COLUMNS FROM rooms LIKE 'closed_at';
-- 3) SELECT status, COUNT(*) FROM rooms GROUP BY status;

ALTER TABLE rooms
  ADD COLUMN status ENUM('open','closed') NOT NULL DEFAULT 'open' AFTER name,
  ADD COLUMN closed_at DATETIME NULL DEFAULT NULL AFTER updated_at;

CREATE INDEX idx_rooms_status ON rooms (status);
