<?php
class Auth {
    public static function check(): bool { return !empty($_SESSION['user']['id']); }
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function id(): ?int { return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null; }
    public static function role(): string { return (string)($_SESSION['user']['role'] ?? ''); }
    public static function isAdmin(): bool { return self::role() === 'admin'; }
    public static function isSupervisor(): bool { return self::role() === 'supervisor'; }
    public static function isPetugas(): bool { return self::role() === 'petugas'; }
    public static function canOperate(): bool { return self::isAdmin() || self::isPetugas(); }
    public static function canMonitor(): bool { return self::isAdmin() || self::isSupervisor(); }
    public static function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'username'=>$user['username'],'role'=>$user['role']];
    }
    public static function logout(): void { $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',(bool)$p['secure'],(bool)$p['httponly']); } session_destroy(); }
}
