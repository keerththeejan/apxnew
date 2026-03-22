-- One-time patch: add service card image support (safe to run once).
-- If you see "Duplicate column name 'image_path'", the column already exists — skip.

ALTER TABLE services
  ADD COLUMN image_path VARCHAR(500) NULL AFTER icon;
