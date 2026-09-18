<?php
// migrations.php juga dipanggil langsung oleh install.php sebelum bootstrap.
// Pastikan helper X-Ray tersedia pada instalasi baru maupun proses migrasi standalone.
if (!function_exists('xray_single_codes')) {
    require_once APP_ROOT . '/core/helpers.php';
}

function copilot_features_create_table(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS copilot_features (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(80) NOT NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        description VARCHAR(500) NOT NULL,
        status ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
        sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_copilot_feature_order(status,sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $seed=$pdo->prepare("INSERT IGNORE INTO copilot_features(code,name,description,status,sort_order) VALUES(?,?,?,'enabled',?)");
    foreach([
        ['code_review','Code Review','Review otomatis untuk perubahan kode dan pull request.',10],
        ['chat','Copilot Chat','Bantuan interaktif untuk debugging, dokumentasi, dan penjelasan kode.',20],
        ['code_generation','Code Generation','Membuat boilerplate, fungsi, refactor, dan test dasar lebih cepat.',30],
        ['pr_summary','Pull Request Summary','Menyediakan ringkasan perubahan dan catatan review pull request.',40],
    ] as $feature) $seed->execute($feature);
}

// Existing migration functions remain below this section.
