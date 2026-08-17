<?php
abstract class Controller {
    protected function view(string $view, array $data = [], string $layout='app'): void {
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        if (!is_file($viewFile)) throw new RuntimeException('View tidak ditemukan: '.$view);
        extract($data, EXTR_SKIP);
        if ($layout === 'none') { require $viewFile; return; }
        ob_start(); require $viewFile; $content = ob_get_clean();
        require APP_ROOT . '/app/views/partials/layout.php';
    }
    protected function redirect(string $path): never { header('Location: '.url($path)); exit; }
    protected function requireLogin(): void { if (!Auth::check()) $this->redirect('login'); }
    protected function requireAdmin(): void { $this->requireLogin(); if (!Auth::isAdmin()) abort(403, 'Akses hanya untuk admin.'); }
    protected function requireSupervisorOrAdmin(): void { $this->requireLogin(); if (!Auth::canMonitor()) abort(403, 'Akses hanya untuk Supervisor atau Admin.'); }
    protected function requireOperator(): void { $this->requireLogin(); if (!Auth::canOperate()) abort(403, 'Supervisor bersifat monitoring. Pengisian logbook hanya untuk Petugas atau Admin.'); }
    protected function requirePetugas(): void { $this->requireLogin(); if (!Auth::isPetugas()) abort(403, 'Akses ini hanya untuk Petugas.'); }
    protected function csrf(): void {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) abort(419, 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}
