<?php
class AuthController extends Controller {
    public function showLogin(): void { if(Auth::check())$this->redirect('');$this->view('auth/login',['title'=>'Login'],'none'); }
    public function login(): void {
        $this->csrf();$u=trim($_POST['username']??'');$p=$_POST['password']??'';$m=new UserModel();$user=$m->findByUsername($u);
        if(!$user||!password_verify($p,$user['password'])){flash('error','Username atau password salah.');$this->redirect('login');}
        Auth::login($user);audit('login','auth',(int)$user['id']);$this->redirect('');
    }
    public function loginQr(): void {
        $this->csrf(); $raw=(string)($_POST['qr']??$_POST['barcode']??''); $token=qr_normalize_login_value($raw);
        if(strlen($token)!==32){ flash('error','QR Code tidak valid. Scan ulang QR Code Petugas.'); $this->redirect('login'); }
        $user=(new UserModel())->findByQrToken($token);
        if(!$user){ flash('error','QR Code tidak dikenali, sudah di-reset, atau akun Petugas tidak aktif.'); $this->redirect('login'); }
        Auth::login($user); audit('login_qr','auth',(int)$user['id']); $this->redirect('');
    }
    public function loginBarcode(): void { $this->loginQr(); }
    public function logout(): void { $this->csrf();audit('logout','auth',Auth::id());Auth::logout();header('Location: '.url('login'));exit; }
}
