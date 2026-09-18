<?php
class SettingsModel extends Model {
    private const DEFAULT_FEATURES = [
        ['code'=>'code_review','name'=>'Code Review','description'=>'Review otomatis untuk perubahan kode dan pull request.','sort_order'=>10],
        ['code'=>'chat','name'=>'Copilot Chat','description'=>'Bantuan interaktif untuk debugging, dokumentasi, dan penjelasan kode.','sort_order'=>20],
        ['code'=>'code_generation','name'=>'Code Generation','description'=>'Membuat boilerplate, fungsi, refactor, dan test dasar lebih cepat.','sort_order'=>30],
        ['code'=>'pr_summary','name'=>'Pull Request Summary','description'=>'Menyediakan ringkasan perubahan dan catatan review pull request.','sort_order'=>40],
    ];

    public function __construct() {
        parent::__construct();
        $this->ensureTable();
    }

    public function copilotFeatures(): array {
        return $this->db->query('SELECT * FROM copilot_features ORDER BY sort_order ASC, name ASC')->fetchAll();
    }

    public function setFeatureStatus(string $code, bool $enabled): void {
        $stmt=$this->db->prepare('UPDATE copilot_features SET status=?, updated_at=NOW() WHERE code=?');
        $stmt->execute([$enabled ? 'enabled' : 'disabled', $code]);
    }

    private function ensureTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS copilot_features (
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

        $stmt=$this->db->prepare('INSERT IGNORE INTO copilot_features(code,name,description,status,sort_order) VALUES(?,?,?,\'enabled\',?)');
        foreach(self::DEFAULT_FEATURES as $feature){
            $stmt->execute([$feature['code'],$feature['name'],$feature['description'],$feature['sort_order']]);
        }
    }
}
