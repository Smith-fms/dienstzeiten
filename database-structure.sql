-- Tabelle für Dienstzeiten
CREATE TABLE IF NOT EXISTS `oc_dienstzeiten_entries` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` VARCHAR(64) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `service_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `station` VARCHAR(255) NOT NULL,
    `other_details` TEXT NULL,
    `overtime_due_to_emergency` BOOLEAN NOT NULL DEFAULT FALSE,
    `emergency_number` VARCHAR(255) NULL,
    `signature` LONGTEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT NULL,
    `approved_by` VARCHAR(64) NULL,
    `approved_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `user_id_idx` (`user_id`),
    INDEX `status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabelle für App-Einstellungen
CREATE TABLE IF NOT EXISTS `oc_dienstzeiten_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `key_idx` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
