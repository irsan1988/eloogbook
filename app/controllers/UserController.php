<?php
class UserController extends Controller {
    private UserModel $m;
    public function __construct(){ $this->m=new UserModel(); }
    public function index(): void { $this->requireAdmin(); $this->view('users/index',['title'=>'Kelola Pengguna','users'=>$this->m->all()]); }
    public function store(): void {
        $this->requireAdmin(); $this->csrf(); $d=$this->payload(true);
        try {
            $id=$this->m->create($d); audit('create','user',$id,['username'=>$d['username'],'role'=>$d['role']]);
            if($d['role']==='petugas'){
                $token=qr_new_token(); $this->m->issueQr($id,$token); $_SESSION['_qr_once'][$id]=$token;
                audit('issue_qr','user',$id); flash('success','Petugas ditambahkan. QR Code login otomatis dibuat. Cetak QR Code lalu atur Penugasan Petugas.');
                $this->redirect('users/'.$id.'/qr');
            }
            flash('success','Pengguna ditambahkan.');
        } catch(Throwable $e){ flash('error','Username sudah digunakan atau data tidak valid: '.$e->getMessage()); }
        $this->redirect('users');
    }
    public function update(string $id): void {
        $this->requireAdmin(); $this->csrf(); $d=$this->payload(false); $this->m->update((int)$id,$d);
        if($d['role']!=='petugas' || !$d['active']){ (new AssignmentModel())->removeUser((int)$id); $this->m->revokeQr((int)$id); }
        audit('update','user',(int)$id,['username'=>$d['username'],'role'=>$d['role']]); flash('success','Pengguna diperbarui.'); $this->redirect('users');
    }
    public function delete(string $id): void {
        $this->requireAdmin(); $this->csrf();
        if((int)$id===Auth::id()){ flash('error','Akun yang sedang digunakan tidak dapat dihapus.'); $this->redirect('users'); }
        try { $this->m->delete((int)$id); audit('delete','user',(int)$id); flash('success','Pengguna dihapus.'); }
        catch(Throwable $e){ flash('error','Pengguna tidak dapat dihapus karena sudah memiliki data. Nonaktifkan saja.'); }
        $this->redirect('users');
    }
    public function issueQr(string $id): void {
        $this->requireAdmin(); $this->csrf(); $u=$this->m->find((int)$id); if(!$u)abort(404,'Pengguna tidak ditemukan.');
        if($u['role']!=='petugas') abort(422,'QR Code login hanya untuk role Petugas.');
        $token=qr_new_token(); $this->m->issueQr((int)$id,$token); $_SESSION['_qr_once'][(int)$id]=$token;
        audit('issue_qr','user',(int)$id); flash('success','QR Code baru dibuat. QR Code sebelumnya langsung tidak berlaku.'); $this->redirect('users/'.$id.'/qr');
    }
    public function qr(string $id): void {
        $this->requireAdmin(); $u=$this->m->find((int)$id); if(!$u)abort(404,'Pengguna tidak ditemukan.');
        if($u['role']!=='petugas')abort(422,'QR Code login hanya untuk role Petugas.');
        $token=(string)($_SESSION['_qr_once'][(int)$id]??'');
        $this->view('users/qr',['title'=>'QR Code Login Petugas','user'=>$u,'token'=>$token]);
    }
    public function issueBarcode(string $id): void { $this->issueQr($id); }
    public function barcode(string $id): void { $this->qr($id); }
    private function payload(bool $new): array {
        $role=$_POST['role']??'petugas';
        $d=['name'=>trim($_POST['name']??''),'username'=>trim($_POST['username']??''),'password'=>$_POST['password']??'','role'=>in_array($role,['admin','supervisor','petugas'],true)?$role:'petugas','active'=>(int)($_POST['active']??1)];
        if($d['name']===''||$d['username']===''||($new&&strlen($d['password'])<8)) abort(422,'Nama, username, dan password minimal 8 karakter untuk pengguna baru wajib diisi.');
        return $d;
    }
}
