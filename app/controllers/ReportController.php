<?php
class ReportController extends Controller {
    public function printRange(): void {
        $this->requireAdmin();
        $logbooks=(new LogbookModel())->all(false);
        $this->view('reports/print_range',[
            'title'=>'Cetak Per Rentang Tanggal',
            'logbooks'=>$logbooks,
            'logbookId'=>(int)($_GET['logbook_id']??0),
            'dateFrom'=>$_GET['date_from']??date('Y-m-01'),
            'dateTo'=>$_GET['date_to']??date('Y-m-d')
        ]);
    }
}
