<?php
declare(strict_types=1);
const APP_ROOT=__DIR__;
require_once APP_ROOT.'/core/helpers.php';
require_once APP_ROOT.'/core/migrations.php';
if(file_exists(APP_ROOT.'/.env.php')){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $host=trim($_POST['db_host']??'localhost');$port=(int)($_POST['db_port']??3306);$name=trim($_POST['db_name']??'');$user=trim($_POST['db_user']??'');$pass=$_POST['db_pass']??'';$admin=trim($_POST['admin_user']??'admin');$adminPass=$_POST['admin_pass']??'';$adminName=trim($_POST['admin_name']??'Administrator');
    try{
        if($name===''||$user===''||strlen($adminPass)<8)throw new RuntimeException('Nama database, user database, dan password admin minimal 8 karakter wajib diisi.');
        $pdo=new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
        $sql=file_get_contents(APP_ROOT.'/database/schema.sql');
        foreach(array_filter(array_map('trim',preg_split('/;\s*(?:\r?\n|$)/',$sql))) as $stmt)$pdo->exec($stmt);
        $s=$pdo->prepare('SELECT id FROM users WHERE username=?');$s->execute([$admin]);$existing=$s->fetch();
        if(!$existing){$s=$pdo->prepare("INSERT INTO users(name,username,password,role,active,created_at) VALUES(?,?,?,'admin',1,NOW())");$s->execute([$adminName,$admin,password_hash($adminPass,PASSWORD_DEFAULT)]);$adminId=(int)$pdo->lastInsertId();}else{$adminId=(int)$existing['id'];}
        // seed logbooks only when empty
        $count=(int)$pdo->query('SELECT COUNT(*) FROM logbooks')->fetchColumn();
        if($count===0){
            $ins=$pdo->prepare("INSERT INTO logbooks(code,name,description,print_layout,orientation,active,created_by,created_at) VALUES(?,?,?,?,?,1,?,NOW())");
            $ins->execute(['HBSC','RANDOM CHECK PEMERIKSAAN HBSCP (HOLD BAGGAGE SECURITY CHECK POINT)','Format awal menyesuaikan contoh buku logbook pemeriksaan HBSCP.','table','landscape',$adminId]);$hb=(int)$pdo->lastInsertId();
            $fi=$pdo->prepare('INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');
            $fields=[
                ['header','Hari / Tanggal','hari_tanggal','date','',1,'',1],
                ['detail','Waktu','waktu','time','',1,'',10],['detail','Metode Pemeriksaan','metode','select',"Manual\nX-Ray\nETD",1,'',20],['detail','Flight','flight','text','',0,'',30],['detail','Jenis Barang','jenis_barang','text','',0,'',40],['detail','Nama Petugas','nama_petugas','text','',0,'',50],['detail','Keterangan','keterangan','text','',0,'',60]
            ];foreach($fields as $f)$fi->execute([$hb,...$f]);
            xray_single_seed($pdo,$adminId);
            xray_single_copy_seeds($pdo,$adminId);
            xray_multi_seed($pdo,$adminId);
            xray_multi_copy_seeds($pdo,$adminId);
        }
        // Tetap pastikan kedua template X-Ray khusus tersedia bila database lama dipakai saat instalasi ulang.
        xray_single_seed($pdo,$adminId);
        xray_single_copy_seeds($pdo,$adminId);
        xray_multi_seed($pdo,$adminId);
        xray_multi_copy_seeds($pdo,$adminId);
        xray_master_create_tables($pdo);
        xray_master_seed_defaults($pdo);
        xray_master_import_existing($pdo);
        $pdo->exec("INSERT INTO app_meta(meta_key,meta_value,updated_at) VALUES('schema_version','18',NOW()) ON DUPLICATE KEY UPDATE meta_value='18',updated_at=NOW()");
        $cfg="<?php\nreturn ".var_export(['app'=>['name'=>'AVSEC Logbook','timezone'=>'Asia/Jakarta','debug'=>false],'db'=>['host'=>$host,'port'=>$port,'name'=>$name,'user'=>$user,'pass'=>$pass]],true).";\n";
        if(file_put_contents(APP_ROOT.'/.env.php',$cfg)===false)throw new RuntimeException('Gagal menulis .env.php. Pastikan folder dapat ditulis sementara.');
        @chmod(APP_ROOT.'/.env.php',0640);
        header('Location: index.php');exit;
    }catch(Throwable$e){$error=$e->getMessage();}
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Instalasi AVSEC Logbook</title><link rel="stylesheet" href="public/assets/app.css"></head><body class="login-page"><div class="login-card" style="width:min(620px,94vw)"><div class="brand center"><div class="brand-mark">A</div><div><b>AVSEC LOGBOOK</b><small>Instalasi Hosting</small></div></div><?php if($error):?><div class="alert danger"><?=htmlspecialchars($error)?></div><?php endif;?><form method="post"><h3>1. Database MySQL</h3><div class="grid2"><div><label class="form-label">Host</label><input class="form-control" name="db_host" value="localhost" required></div><div><label class="form-label">Port</label><input class="form-control" type="number" name="db_port" value="3306" required></div></div><label class="form-label mt">Nama Database</label><input class="form-control" name="db_name" placeholder="cpaneluser_avsec" required><div class="grid2 mt"><div><label class="form-label">User Database</label><input class="form-control" name="db_user" required></div><div><label class="form-label">Password Database</label><input class="form-control" type="password" name="db_pass"></div></div><hr><h3>2. Akun Admin</h3><label class="form-label">Nama Admin</label><input class="form-control" name="admin_name" value="Administrator" required><div class="grid2 mt"><div><label class="form-label">Username</label><input class="form-control" name="admin_user" value="admin" required></div><div><label class="form-label">Password min. 8 karakter</label><input class="form-control" type="password" name="admin_pass" minlength="8" required></div></div><button class="btn btn-primary full mt-lg">INSTAL SEKARANG</button></form></div></body></html>