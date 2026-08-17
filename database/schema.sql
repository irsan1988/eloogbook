SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 username VARCHAR(80) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','supervisor','petugas') NOT NULL DEFAULT 'petugas',
 active TINYINT(1) NOT NULL DEFAULT 1,
 qr_token_hash CHAR(64) NULL UNIQUE,
 qr_issued_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbooks (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(40) NOT NULL UNIQUE,
 name VARCHAR(200) NOT NULL,
 description TEXT NULL,
 print_layout ENUM('table','form') NOT NULL DEFAULT 'table',
 orientation ENUM('portrait','landscape') NOT NULL DEFAULT 'landscape',
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_logbook_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_petugas_assignments (
 logbook_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 assigned_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(logbook_id,user_id),
 INDEX idx_general_assignment_user(user_id),
 CONSTRAINT fk_general_assignment_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
 CONSTRAINT fk_general_assignment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_general_assignment_admin FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_check_petugas_assignments (
 logbook_id INT UNSIGNED PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 assigned_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_daily_assignment_user(user_id),
 CONSTRAINT fk_daily_assignment_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
 CONSTRAINT fk_daily_assignment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_daily_assignment_admin FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_fields (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 logbook_id INT UNSIGNED NOT NULL,
 section ENUM('header','detail') NOT NULL DEFAULT 'detail',
 label VARCHAR(150) NOT NULL,
 field_key VARCHAR(100) NOT NULL,
 field_type ENUM('text','textarea','number','date','time','datetime-local','select','checkbox') NOT NULL DEFAULT 'text',
 options TEXT NULL,
 required TINYINT(1) NOT NULL DEFAULT 0,
 help_text VARCHAR(255) NULL,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_field_key(logbook_id,field_key),
 CONSTRAINT fk_field_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_sessions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 logbook_id INT UNSIGNED NOT NULL,
 session_date DATE NOT NULL,
 shift VARCHAR(80) NOT NULL DEFAULT '',
 daily_once_key VARCHAR(191) NULL,
 created_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_session_date(session_date),
 INDEX idx_session_logbook(logbook_id),
 UNIQUE KEY uq_logbook_date_shift(logbook_id,session_date,shift),
 UNIQUE KEY uq_daily_once_key(daily_once_key),
 CONSTRAINT fk_session_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE RESTRICT,
 CONSTRAINT fk_session_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_session_values (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 session_id BIGINT UNSIGNED NOT NULL,
 field_id INT UNSIGNED NOT NULL,
 value MEDIUMTEXT NULL,
 UNIQUE KEY uq_session_field(session_id,field_id),
 CONSTRAINT fk_sv_session FOREIGN KEY (session_id) REFERENCES logbook_sessions(id) ON DELETE CASCADE,
 CONSTRAINT fk_sv_field FOREIGN KEY (field_id) REFERENCES logbook_fields(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_rows (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 session_id BIGINT UNSIGNED NOT NULL,
 sequence_no INT NOT NULL,
 single_entry_key VARCHAR(191) NULL,
 created_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_session_sequence(session_id,sequence_no),
 UNIQUE KEY uq_single_entry_key(single_entry_key),
 CONSTRAINT fk_row_session FOREIGN KEY (session_id) REFERENCES logbook_sessions(id) ON DELETE CASCADE,
 CONSTRAINT fk_row_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logbook_row_values (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 row_id BIGINT UNSIGNED NOT NULL,
 field_id INT UNSIGNED NOT NULL,
 value MEDIUMTEXT NULL,
 UNIQUE KEY uq_row_field(row_id,field_id),
 CONSTRAINT fk_rv_row FOREIGN KEY (row_id) REFERENCES logbook_rows(id) ON DELETE CASCADE,
 CONSTRAINT fk_rv_field FOREIGN KEY (field_id) REFERENCES logbook_fields(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS hidden_logbook_sessions (
 user_id INT UNSIGNED NOT NULL,
 session_id BIGINT UNSIGNED NOT NULL,
 hidden_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,session_id),
 CONSTRAINT fk_hidden_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_hidden_session FOREIGN KEY (session_id) REFERENCES logbook_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS xray_master_operators (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 value VARCHAR(191) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_value(value),
 INDEX idx_active_order(active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS xray_master_locations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 value VARCHAR(191) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_value(value),
 INDEX idx_active_order(active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS xray_master_machines (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 value VARCHAR(191) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_value(value),
 INDEX idx_active_order(active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS xray_master_certificates (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 value VARCHAR(191) NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_value(value),
 INDEX idx_active_order(active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(50) NOT NULL,
 entity VARCHAR(80) NOT NULL,
 entity_id BIGINT NULL,
 meta TEXT NULL,
 ip_address VARCHAR(45) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_created(created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_meta (
 meta_key VARCHAR(80) PRIMARY KEY,
 meta_value TEXT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
