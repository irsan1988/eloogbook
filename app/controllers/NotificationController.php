<?php
class NotificationController extends Controller {
    public function index(): void {
        $this->requireSupervisorOrAdmin();
        $from=$_GET['date_from']??date('Y-m-d',strtotime('-29 days'));
        $to=$_GET['date_to']??date('Y-m-d');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)) abort(422,'Rentang tanggal tidak valid.');
        $report=(new MonitoringModel())->missingReport($from,$to);
        $this->view('notifications/index',['title'=>'Notifikasi Pengisian','report'=>$report,'dateFrom'=>$from,'dateTo'=>$to]);
    }
}
