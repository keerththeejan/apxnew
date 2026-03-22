-- Fix: Unknown column 'parent_id' in 'field list'
-- Run in phpMyAdmin (your database). If a line errors with "Duplicate column", skip it.

ALTER TABLE nav_items ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE nav_items ADD COLUMN slug VARCHAR(190) NULL AFTER url;
ALTER TABLE nav_items ADD COLUMN icon VARCHAR(80) NULL AFTER label;
ALTER TABLE nav_items ADD COLUMN is_button TINYINT(1) NOT NULL DEFAULT 0 AFTER open_new_tab;
ALTER TABLE nav_items ADD KEY idx_nav_parent (parent_id);
ALTER TABLE nav_items ADD CONSTRAINT fk_nav_parent FOREIGN KEY (parent_id) REFERENCES nav_items(id) ON DELETE SET NULL ON UPDATE CASCADE;
