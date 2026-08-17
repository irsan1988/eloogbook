<?php
// migrations.php juga dipanggil langsung oleh install.php sebelum bootstrap.
// Pastikan helper X-Ray tersedia pada instalasi baru maupun proses migrasi standalone.
if (!function_exists('xray_single_codes')) {
    require_once APP_ROOT . '/core/helpers.php';
}
function xray_single_seed_named(PDO $pdo, int $adminId, string $code, string $name, string $description): int {
    $check=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
    $check->execute([$code]);
    $existing=$check->fetchColumn();
    if($existing) return (int)$existing;

    $ins=$pdo->prepare("INSERT INTO logbooks(code,name,description,print_layout,orientation,active,created_by,created_at) VALUES(?,?,?,?,?,1,?,NOW())");
    $ins->execute([$code,$name,$description,'form','portrait',($adminId>0?$adminId:null)]);
    $logbookId=(int)$pdo->lastInsertId();

    $fi=$pdo->prepare('INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');
    $headers=[
        ['header','Nama Operator Penerbangan','operator','select','',1,'Pilihan dari Master Data X-Ray.',10],
        ['header','Waktu Pengujian','waktu_pengujian','time','',1,'',20],
        ['header','Lokasi Penempatan/Gedung','lokasi','select','',1,'Pilihan dari Master Data X-Ray.',30],
        ['header','Merk/Tipe/Nomor Seri','mesin','select','',1,'Pilihan dari Master Data X-Ray.',40],
        ['header','Nomor dan Tanggal Sertifikat','sertifikat','select','',0,'Pilihan dari Master Data X-Ray.',50],
    ];
    foreach($headers as $f) $fi->execute([$logbookId,...$f]);

    $order=100;$checkboxes=[];$checkboxes[]=['TEST 2a','box_t2a'];
    foreach([14,16,18,20,22,24,26,28,30] as $n) $checkboxes[]=['TEST 3 AWG '.$n,'box_t3_'.$n];
    $checkboxes[]=['TEST 2b','box_t2b'];
    foreach([36,32,30,24] as $row) for($col=1;$col<=4;$col++) $checkboxes[]=['TEST 1 '.$row.' / kotak '.$col,'box_t1_'.$row.'_'.$col];
    foreach([
        ['TEST 4 - 1.5 mm horizontal','box_t4_15_h'],['TEST 4 - 1.5 mm vertikal','box_t4_15_v'],
        ['TEST 4 - 2.0 mm horizontal','box_t4_20_h'],['TEST 4 - 2.0 mm vertikal','box_t4_20_v'],
        ['TEST 4 - 1.0 mm horizontal','box_t4_10_h'],['TEST 4 - 1.0 mm vertikal','box_t4_10_v'],
        ['TEST 5 - 0.65 mm','box_t5_065'],['TEST 5 - 0.10 mm','box_t5_010'],['TEST 5 - 0.15 mm','box_t5_015'],
        ['HASIL PASS','result_pass'],['HASIL FAIL','result_fail'],
    ] as $x) $checkboxes[]=$x;
    foreach($checkboxes as [$label,$key]){$fi->execute([$logbookId,'detail',$label,$key,'checkbox','',0,'Centang langsung pada kotak di gambar.',$order]);$order+=10;}
    $fi->execute([$logbookId,'detail','Catatan','catatan','textarea','',0,'Isi apabila diperlukan.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Personel Pengamanan Penerbangan 1','personel_1','text','',0,'Nama personel pertama.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Tanda Tangan Personel Pengamanan Penerbangan 1','ttd_personel_1','textarea','',0,'Tanda tangan langsung pada signature pad.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Personel Pengamanan Penerbangan 2','personel_2','text','',0,'Nama personel kedua.',$order]);
    return $logbookId;
}

function xray_single_seed(PDO $pdo, int $adminId): int {
    return xray_single_seed_named(
        $pdo,$adminId,
        'XRAY-SINGLE-BAGASI',
        'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW',
        'Template khusus X-Ray Single View Bagasi. Pengisian TEST dilakukan dengan mencentang langsung kotak kecil pada gambar. Hasil cetak mengikuti lembar X-Ray Single View.'
    );
}

function xray_single_copy_seeds(PDO $pdo, int $adminId): array {
    return [
        xray_single_seed_named(
            $pdo,$adminId,
            'XRAY-SINGLE-CABIN',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS SINGLE VIEW',
            'Salinan template X-Ray Single View untuk area Cabin. Struktur pengisian, checkbox visual, PASS/FAIL, catatan, personel, dan tata letak cetak sama dengan Bagasi Single View.'
        ),
        xray_single_seed_named(
            $pdo,$adminId,
            'XRAY-SINGLE-SSCP',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS SINGLE VIEW',
            'Salinan template X-Ray Single View untuk SSCP. Struktur pengisian, checkbox visual, PASS/FAIL, catatan, personel, dan tata letak cetak sama dengan Bagasi Single View.'
        ),
        xray_single_seed_named(
            $pdo,$adminId,
            'XRAY-SINGLE-CARGO',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS SINGLE VIEW',
            'Salinan template X-Ray Single View untuk Cargo. Struktur pengisian, checkbox visual, PASS/FAIL, catatan, personel, dan tata letak cetak sama dengan Bagasi Single View.'
        ),
    ];
}

function xray_multi_seed_named(PDO $pdo, int $adminId, string $code, string $name, string $description): int {
    $check=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
    $check->execute([$code]);
    $existing=$check->fetchColumn();
    if($existing) return (int)$existing;

    $ins=$pdo->prepare("INSERT INTO logbooks(code,name,description,print_layout,orientation,active,created_by,created_at) VALUES(?,?,?,?,?,1,?,NOW())");
    $ins->execute([
        $code,
        $name,
        $description,
        'form','portrait',($adminId>0?$adminId:null)
    ]);
    $logbookId=(int)$pdo->lastInsertId();

    $fi=$pdo->prepare('INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');
    $headers=[
        ['header','Nama Operator Penerbangan','operator','select','',1,'Pilihan dari Master Data X-Ray.',10],
        ['header','Waktu Pengujian','waktu_pengujian','time','',1,'',20],
        ['header','Lokasi Penempatan/Gedung','lokasi','select','',1,'Pilihan dari Master Data X-Ray.',30],
        ['header','Merk/Tipe/Nomor Seri','mesin','select','',1,'Pilihan dari Master Data X-Ray.',40],
        ['header','Nomor dan Tanggal Sertifikat','sertifikat','select','',0,'Pilihan dari Master Data X-Ray.',50],
    ];
    foreach($headers as $f) $fi->execute([$logbookId,...$f]);

    $order=100;
    $boxes=[];
    foreach(['u'=>'Generator Atas/Bawah','l'=>'Generator Samping'] as $prefix=>$group){
        $boxes[]=["$group - TEST 2a","{$prefix}_t2a"];
        foreach([14,16,18,20,22,24,26,28,30] as $n) $boxes[]=["$group - TEST 3 AWG $n","{$prefix}_t3_$n"];
        $boxes[]=["$group - TEST 2b","{$prefix}_t2b"];
        foreach([36,32,30,24] as $row) for($col=1;$col<=4;$col++) $boxes[]=["$group - TEST 1 AWG $row kotak $col","{$prefix}_t1_{$row}_{$col}"];
        foreach([
            ['TEST 4 - 1.5 mm horizontal','t4_15_h'],['TEST 4 - 1.5 mm vertikal','t4_15_v'],
            ['TEST 4 - 2.0 mm horizontal','t4_20_h'],['TEST 4 - 2.0 mm vertikal','t4_20_v'],
            ['TEST 4 - 1.0 mm horizontal','t4_10_h'],['TEST 4 - 1.0 mm vertikal','t4_10_v'],
            ['TEST 5 - 0.65 mm','t5_065'],['TEST 5 - 0.10 mm','t5_010'],['TEST 5 - 0.15 mm','t5_015'],
        ] as [$label,$key]) $boxes[]=["$group - $label","{$prefix}_{$key}"];
    }
    $boxes[]=['HASIL PASS','result_pass'];
    $boxes[]=['HASIL FAIL','result_fail'];

    foreach($boxes as [$label,$key]){
        $fi->execute([$logbookId,'detail',$label,$key,'checkbox','',0,'Centang langsung pada kotak kecil yang tersedia di gambar.',$order]);
        $order+=10;
    }
    $fi->execute([$logbookId,'detail','Catatan','catatan','textarea','',0,'Isi catatan langsung pada area CATATAN di template.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Personel Pengamanan Penerbangan 1','personel_1','text','',0,'Nama personel pertama.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Tanda Tangan Personel Pengamanan Penerbangan 1','ttd_personel_1','textarea','',0,'Tanda tangan langsung pada area tanda tangan di template.',$order]);$order+=10;
    $fi->execute([$logbookId,'detail','Personel Pengamanan Penerbangan 2','personel_2','text','',0,'Nama personel kedua.',$order]);

    return $logbookId;
}

function xray_multi_seed(PDO $pdo, int $adminId): int {
    return xray_multi_seed_named(
        $pdo,$adminId,
        'XRAY-MULTI-BAGASI',
        'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW',
        'Template khusus halaman pertama. Generator atas/bawah dan generator samping diisi dengan mencentang kotak langsung pada gambar. Hasil cetak mengikuti lembar asli halaman 58.'
    );
}

function xray_multi_copy_seeds(PDO $pdo, int $adminId): array {
    return [
        xray_multi_seed_named(
            $pdo,$adminId,
            'XRAY-MULTI-CABIN',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS MULTI VIEW',
            'Salinan template X-Ray Multi View untuk area Cabin. Struktur pengisian dan posisi checkbox sama dengan Daily Check Multi View.'
        ),
        xray_multi_seed_named(
            $pdo,$adminId,
            'XRAY-MULTI-SSCP',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS MULTI VIEW',
            'Salinan template X-Ray Multi View untuk SSCP. Struktur pengisian dan posisi checkbox sama dengan Daily Check Multi View.'
        ),
        xray_multi_seed_named(
            $pdo,$adminId,
            'XRAY-MULTI-CARGO',
            'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS MULTI VIEW',
            'Salinan template X-Ray Multi View untuk Cargo. Struktur pengisian dan posisi checkbox sama dengan Daily Check Multi View.'
        ),
    ];
}

function xray_master_create_tables(PDO $pdo): void {
    $tables=['xray_master_operators','xray_master_locations','xray_master_machines','xray_master_certificates'];
    foreach($tables as $table){
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$table} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(191) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_value(value),
            INDEX idx_active_order(active,sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

function xray_master_seed_defaults(PDO $pdo): void {
    $defaults=[
        'xray_master_operators'=>['UPBU Rembele'],
        'xray_master_locations'=>['HB SCP','SCP / Kargo'],
        'xray_master_machines'=>['X-RAY CENTER / XRC 100-100 DV / 86913542123006','Smiths Detection'],
        'xray_master_certificates'=>[],
    ];
    foreach($defaults as $table=>$values){
        $ins=$pdo->prepare("INSERT IGNORE INTO {$table}(value,active,sort_order,created_at,updated_at) VALUES(?,1,0,NOW(),NOW())");
        foreach($values as $value) $ins->execute([$value]);
    }
}

function xray_master_import_existing(PDO $pdo): void {
    $map=[
        'operator'=>'xray_master_operators',
        'lokasi'=>'xray_master_locations',
        'mesin'=>'xray_master_machines',
        'sertifikat'=>'xray_master_certificates',
    ];
    $codes=array_values(array_unique(array_merge(xray_single_codes(),xray_multi_codes())));
    if(!$codes) return;
    $ph=implode(',',array_fill(0,count($codes),'?'));
    foreach($map as $fieldKey=>$table){
        $sql="SELECT DISTINCT TRIM(sv.value) value
              FROM logbook_session_values sv
              JOIN logbook_fields f ON f.id=sv.field_id
              JOIN logbooks l ON l.id=f.logbook_id
              WHERE f.field_key=? AND l.code IN ({$ph}) AND TRIM(COALESCE(sv.value,''))<>''";
        $q=$pdo->prepare($sql);
        $q->execute(array_merge([$fieldKey],$codes));
        $ins=$pdo->prepare("INSERT IGNORE INTO {$table}(value,active,sort_order,created_at,updated_at) VALUES(?,1,0,NOW(),NOW())");
        foreach($q->fetchAll(PDO::FETCH_COLUMN) as $value){
            $value=trim((string)$value);
            if($value!=='' && mb_strlen($value)<=191) $ins->execute([$value]);
        }
    }
}

function run_app_migrations(): void {
    $pdo=Database::conn();
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_meta (meta_key VARCHAR(80) PRIMARY KEY, meta_value TEXT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $q=$pdo->prepare('SELECT meta_value FROM app_meta WHERE meta_key=?');$q->execute(['schema_version']);$version=(int)($q->fetchColumn() ?: 0);

    if($version < 4){
        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        xray_single_seed($pdo,$adminId);
        $version=4;
    }

    if($version < 5){
        $pdo->exec("ALTER TABLE users MODIFY role ENUM('admin','supervisor','petugas') NOT NULL DEFAULT 'petugas'");
        $pdo->exec("CREATE TABLE IF NOT EXISTS hidden_logbook_sessions (
            user_id INT UNSIGNED NOT NULL,
            session_id BIGINT UNSIGNED NOT NULL,
            hidden_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(user_id,session_id),
            CONSTRAINT fk_hidden_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_hidden_session FOREIGN KEY (session_id) REFERENCES logbook_sessions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $version=5;
    }

    if($version < 6){
        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        xray_multi_seed($pdo,$adminId);
        $version=6;
    }

    if($version < 7){
        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        $rename=$pdo->prepare('UPDATE logbooks SET name=? WHERE code=?');
        $rename->execute(['DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW','XRAY-MULTI-EXACT']);
        xray_multi_copy_seeds($pdo,$adminId);
        $version=7;
    }

    if($version < 8){
        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        $rename=$pdo->prepare('UPDATE logbooks SET name=? WHERE code=?');
        $rename->execute(['DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW','XRAY-SINGLE-EXACT']);
        xray_single_copy_seeds($pdo,$adminId);
        $version=8;
    }


    if($version < 9){
        // Signature pad Personel 1 untuk seluruh Daily Check X-Ray Single dan Multi View.
        // MEDIUMTEXT dipakai agar data PNG base64 tidak terpotong pada hosting.
        $pdo->exec("ALTER TABLE logbook_row_values MODIFY value MEDIUMTEXT NULL");
        $codes=array_merge(xray_single_codes(),xray_multi_codes());
        $find=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
        $exists=$pdo->prepare('SELECT id FROM logbook_fields WHERE logbook_id=? AND field_key=? LIMIT 1');
        $maxOrder=$pdo->prepare("SELECT COALESCE(MAX(sort_order),0) FROM logbook_fields WHERE logbook_id=? AND section='detail'");
        $add=$pdo->prepare("INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,'detail','Tanda Tangan Personel Pengamanan Penerbangan 1','ttd_personel_1','textarea','',0,'Tanda tangan langsung pada signature pad.',?,NOW())");
        foreach($codes as $code){
            $find->execute([$code]); $lid=(int)($find->fetchColumn()?:0); if($lid<=0) continue;
            $exists->execute([$lid,'ttd_personel_1']); if($exists->fetchColumn()) continue;
            $maxOrder->execute([$lid]); $order=(int)$maxOrder->fetchColumn()+10;
            $add->execute([$lid,$order]);
        }
        $version=9;
    }

    if($version < 11){
        // v11: Daily Check X-Ray menjadi template tetap sistem.
        // Restore metadata kanonik agar instalasi lama yang pernah mengubah nama/layout kembali konsisten.
        $fixed=[
            'XRAY-SINGLE-BAGASI'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW',
            'XRAY-SINGLE-EXACT'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW',
            'XRAY-SINGLE-ACT'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW',
            'XRAY-SINGLE-CABIN'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS SINGLE VIEW',
            'XRAY-SINGLE-SSCP'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS SINGLE VIEW',
            'XRAY-SINGLE-CARGO'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS SINGLE VIEW',
            'XRAY-MULTI-BAGASI'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW',
            'XRAY-MULTI-EXACT'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW',
            'XRAY-MULTI-CABIN'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS MULTI VIEW',
            'XRAY-MULTI-SSCP'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS MULTI VIEW',
            'XRAY-MULTI-CARGO'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS MULTI VIEW',
        ];
        $restore=$pdo->prepare("UPDATE logbooks SET name=?, print_layout='form', orientation='portrait', active=1 WHERE code=?");
        foreach($fixed as $code=>$name){ $restore->execute([$name,$code]); }
        $version=11;
    }

    if($version < 12){
        // v12: Catatan, Personel, dan tanda tangan diisi langsung pada template X-Ray.
        // Multi View versi lama belum selalu memiliki field Catatan, jadi tambahkan tanpa mengubah data yang sudah ada.
        $find=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
        $exists=$pdo->prepare('SELECT id FROM logbook_fields WHERE logbook_id=? AND field_key=? LIMIT 1');
        $maxOrder=$pdo->prepare("SELECT COALESCE(MAX(sort_order),0) FROM logbook_fields WHERE logbook_id=? AND section='detail'");
        $add=$pdo->prepare("INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,'detail','Catatan','catatan','textarea','',0,'Isi catatan langsung pada area CATATAN di template.',?,NOW())");
        foreach(xray_multi_codes() as $code){
            $find->execute([$code]); $lid=(int)($find->fetchColumn()?:0); if($lid<=0) continue;
            $exists->execute([$lid,'catatan']); if($exists->fetchColumn()) continue;
            $maxOrder->execute([$lid]); $order=(int)$maxOrder->fetchColumn()+10;
            $add->execute([$lid,$order]);
        }
        $version=12;
    }

    if($version < 13){
        // v13: pisahkan Daily Check X-Ray ke menu sendiri dan gunakan kode BAGASI yang eksplisit.
        // Perubahan kode tidak mengubah ID logbook, sehingga sesi dan data lama tetap terhubung.
        $codeExists=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
        $renameCode=$pdo->prepare('UPDATE logbooks SET code=? WHERE code=?');

        $renames=[
            ['XRAY-SINGLE-EXACT','XRAY-SINGLE-BAGASI'],
            ['XRAY-SINGLE-ACT','XRAY-SINGLE-BAGASI'],
            ['XRAY-MULTI-EXACT','XRAY-MULTI-BAGASI'],
        ];
        foreach($renames as [$old,$new]){
            $codeExists->execute([$old]); $oldId=(int)($codeExists->fetchColumn()?:0);
            if($oldId<=0) continue;
            $codeExists->execute([$new]); $newId=(int)($codeExists->fetchColumn()?:0);
            if($newId<=0){
                $renameCode->execute([$new,$old]);
            } elseif($newId!==$oldId) {
                // Kasus tidak normal: kode baru sudah ada. Pertahankan data lama dan nonaktifkan duplikat legacy
                // agar menu Daily Check tetap hanya menampilkan template kanonik.
                $pdo->prepare("UPDATE logbooks SET active=0, description=CONCAT(COALESCE(description,''),' [LEGACY CODE: ',?,']') WHERE id=?")
                    ->execute([$old,$oldId]);
            }
        }

        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        xray_single_seed($pdo,$adminId);
        xray_single_copy_seeds($pdo,$adminId);
        xray_multi_seed($pdo,$adminId);
        xray_multi_copy_seeds($pdo,$adminId);

        $fixed=[
            'XRAY-SINGLE-BAGASI'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS SINGLE VIEW',
            'XRAY-SINGLE-CABIN'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS SINGLE VIEW',
            'XRAY-SINGLE-SSCP'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS SINGLE VIEW',
            'XRAY-SINGLE-CARGO'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS SINGLE VIEW',
            'XRAY-MULTI-BAGASI'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY BAGASI JENIS MULTI VIEW',
            'XRAY-MULTI-CABIN'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CABIN JENIS MULTI VIEW',
            'XRAY-MULTI-SSCP'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY SSCP JENIS MULTI VIEW',
            'XRAY-MULTI-CARGO'=>'DIALY CHECK PENGUJIAN HARIAN MESIN X-RAY CARGO JENIS MULTI VIEW',
        ];
        $restore=$pdo->prepare("UPDATE logbooks SET name=?, print_layout='form', orientation='portrait', active=1 WHERE code=?");
        foreach($fixed as $code=>$name){ $restore->execute([$name,$code]); }
        $version=13;
    }

    if($version < 14){
        // v14: Empat data identitas Daily Check X-Ray menjadi master data database tersendiri.
        // Nilai lama diimpor otomatis agar riwayat tetap dapat dipilih saat header diedit.
        xray_master_create_tables($pdo);
        xray_master_seed_defaults($pdo);
        xray_master_import_existing($pdo);

        $codes=array_values(array_unique(array_merge(xray_single_codes(),xray_multi_codes())));
        $find=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
        $upd=$pdo->prepare("UPDATE logbook_fields SET field_type='select', options='', help_text='Pilihan dari Master Data X-Ray.' WHERE logbook_id=? AND section='header' AND field_key=?");
        foreach($codes as $code){
            $find->execute([$code]); $lid=(int)($find->fetchColumn()?:0); if($lid<=0) continue;
            foreach(['operator','lokasi','mesin','sertifikat'] as $key) $upd->execute([$lid,$key]);
        }
        $version=14;
    }

    if($version < 15){
        // v15: Daily Check X-Ray hanya satu sesi dan satu lembar per jenis X-Ray per tanggal.
        // Kolom key bersifat NULL untuk logbook umum, sehingga alur multi-baris logbook umum tidak berubah.
        $col=$pdo->query("SHOW COLUMNS FROM logbook_sessions LIKE 'daily_once_key'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE logbook_sessions ADD COLUMN daily_once_key VARCHAR(191) NULL AFTER shift");
        $idx=$pdo->query("SHOW INDEX FROM logbook_sessions WHERE Key_name='uq_daily_once_key'")->fetch();
        if(!$idx) $pdo->exec("ALTER TABLE logbook_sessions ADD UNIQUE KEY uq_daily_once_key(daily_once_key)");

        $col=$pdo->query("SHOW COLUMNS FROM logbook_rows LIKE 'single_entry_key'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE logbook_rows ADD COLUMN single_entry_key VARCHAR(191) NULL AFTER sequence_no");
        $idx=$pdo->query("SHOW INDEX FROM logbook_rows WHERE Key_name='uq_single_entry_key'")->fetch();
        if(!$idx) $pdo->exec("ALTER TABLE logbook_rows ADD UNIQUE KEY uq_single_entry_key(single_entry_key)");

        $version=15;
    }

    if($version < 16){
        // v16: penugasan Petugas per logbook + satu Petugas untuk setiap Daily Check X-Ray + login barcode.
        $col=$pdo->query("SHOW COLUMNS FROM users LIKE 'barcode_token_hash'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE users ADD COLUMN barcode_token_hash CHAR(64) NULL AFTER active");
        $col=$pdo->query("SHOW COLUMNS FROM users LIKE 'barcode_issued_at'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE users ADD COLUMN barcode_issued_at DATETIME NULL AFTER barcode_token_hash");
        $idx=$pdo->query("SHOW INDEX FROM users WHERE Key_name='uq_users_barcode_token_hash'")->fetch();
        if(!$idx) $pdo->exec("ALTER TABLE users ADD UNIQUE KEY uq_users_barcode_token_hash(barcode_token_hash)");

        $pdo->exec("CREATE TABLE IF NOT EXISTS logbook_petugas_assignments (
            logbook_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            assigned_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(logbook_id,user_id),
            INDEX idx_general_assignment_user(user_id),
            CONSTRAINT fk_general_assignment_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
            CONSTRAINT fk_general_assignment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_general_assignment_admin FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS daily_check_petugas_assignments (
            logbook_id INT UNSIGNED PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            assigned_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_daily_assignment_user(user_id),
            CONSTRAINT fk_daily_assignment_logbook FOREIGN KEY (logbook_id) REFERENCES logbooks(id) ON DELETE CASCADE,
            CONSTRAINT fk_daily_assignment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_daily_assignment_admin FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Pertahankan perilaku logbook umum versi lama: petugas aktif yang sudah ada tetap mendapat akses
        // ke logbook umum. Admin dapat mempersempit penugasan setelah update.
        $adminId=(int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 0);
        $pdo->exec("INSERT IGNORE INTO logbook_petugas_assignments(logbook_id,user_id,assigned_by,created_at)
            SELECT l.id,u.id,".$adminId.",NOW() FROM logbooks l CROSS JOIN users u
            WHERE l.code NOT LIKE 'XRAY-%' AND u.role='petugas' AND u.active=1");

        // Daily Check hanya satu petugas. Untuk data lama, pilih petugas terakhir yang benar-benar
        // mengisi baris pada jenis Daily Check tersebut. Jika belum ada riwayat, biarkan belum ditugaskan.
        $findLog=$pdo->prepare('SELECT id FROM logbooks WHERE code=? LIMIT 1');
        $findLast=$pdo->prepare("SELECT r.created_by FROM logbook_rows r
            JOIN logbook_sessions s ON s.id=r.session_id
            JOIN users u ON u.id=r.created_by
            WHERE s.logbook_id=? AND u.role='petugas' AND u.active=1
            ORDER BY r.created_at DESC,r.id DESC LIMIT 1");
        $insDaily=$pdo->prepare('INSERT IGNORE INTO daily_check_petugas_assignments(logbook_id,user_id,assigned_by,created_at,updated_at) VALUES(?,?,?,NOW(),NOW())');
        foreach(array_values(array_unique(array_merge(xray_single_codes(),xray_multi_codes()))) as $code){
            $findLog->execute([$code]); $lid=(int)($findLog->fetchColumn()?:0); if($lid<=0)continue;
            $findLast->execute([$lid]); $uid=(int)($findLast->fetchColumn()?:0);
            if($uid>0) $insDaily->execute([$lid,$uid,$adminId?:null]);
        }
        $version=16;
    }

    if($version < 17){
        // v17: perubahan aplikasi. Admin dapat menghapus permanen template Daily Check X-Ray.
        // Tidak ada perubahan struktur database. Template yang dihapus tidak dibuat ulang otomatis
        // pada penggunaan normal, sehingga keputusan penghapusan Admin tetap dipertahankan.
        $version=17;
    }

    if($version < 18){
        // v18: kredensial login Petugas beralih dari barcode 1D ke QR Code.
        // Gunakan kolom baru agar barcode lama otomatis tidak lagi menjadi kredensial aktif.
        // Admin perlu membuat QR Code baru satu kali untuk Petugas yang sudah ada.
        $col=$pdo->query("SHOW COLUMNS FROM users LIKE 'qr_token_hash'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE users ADD COLUMN qr_token_hash CHAR(64) NULL AFTER active");
        $col=$pdo->query("SHOW COLUMNS FROM users LIKE 'qr_issued_at'")->fetch();
        if(!$col) $pdo->exec("ALTER TABLE users ADD COLUMN qr_issued_at DATETIME NULL AFTER qr_token_hash");
        $idx=$pdo->query("SHOW INDEX FROM users WHERE Key_name='uq_users_qr_token_hash'")->fetch();
        if(!$idx) $pdo->exec("ALTER TABLE users ADD UNIQUE KEY uq_users_qr_token_hash(qr_token_hash)");
        $version=18;
    }

    $u=$pdo->prepare("INSERT INTO app_meta(meta_key,meta_value,updated_at) VALUES('schema_version',?,NOW()) ON DUPLICATE KEY UPDATE meta_value=VALUES(meta_value),updated_at=NOW()");
    $u->execute([(string)$version]);
}
