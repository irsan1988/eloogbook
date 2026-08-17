<?php
class PrintController extends Controller {
    public function session(string $sessionId): void {
        $this->requireLogin();
        $e = new EntryModel();
        $l = new LogbookModel();
        $s = $e->session((int)$sessionId);
        if (!$s) abort(404, 'Sesi tidak ditemukan.');
        if(Auth::isPetugas()){
            $daily=is_xray_special_code((string)($s['code']??''));
            if(!(new AssignmentModel())->isAssigned((int)Auth::id(),(int)$s['logbook_id'],$daily)) abort(403,'Anda tidak ditugaskan untuk logbook ini.');
        }
        $data = [
            'title'        => 'Cetak '.$s['logbook_name'],
            'session'      => $s,
            'headerFields' => $l->fields((int)$s['logbook_id'], 'header'),
            'detailFields' => $l->fields((int)$s['logbook_id'], 'detail'),
            'headerValues' => $e->sessionValues((int)$sessionId),
            'rows'         => $e->rows((int)$sessionId)
        ];
        $code=$s['code']??''; $view = is_xray_single_code($code) ? 'print/xray_single' : (is_xray_multi_code($code) ? 'print/xray_multi' : 'print/session');
        $this->view($view, $data, 'none');
        audit('print', 'session', (int)$sessionId);
    }

    public function range(): void {
        $this->requireAdmin();
        $logbookId=(int)($_GET['logbook_id']??0);
        $dateFrom=$_GET['date_from']??'';
        $dateTo=$_GET['date_to']??'';
        if($logbookId<=0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateTo)) abort(422,'Logbook dan rentang tanggal wajib diisi.');
        if($dateFrom>$dateTo) [$dateFrom,$dateTo]=[$dateTo,$dateFrom];

        $e=new EntryModel(); $l=new LogbookModel();
        $lb=$l->find($logbookId); if(!$lb) abort(404,'Logbook tidak ditemukan.');
        $sessions=$e->sessionsForRange($logbookId,$dateFrom,$dateTo);
        $bundle=[];
        foreach($sessions as $s){
            $bundle[]=[
                'session'=>$s,
                'headerValues'=>$e->sessionValues((int)$s['id']),
                'rows'=>$e->rows((int)$s['id'])
            ];
        }
        $this->view('print/range',[
            'title'=>'Cetak '.$lb['name'].' '.$dateFrom.' s.d. '.$dateTo,
            'logbook'=>$lb,
            'headerFields'=>$l->fields($logbookId,'header'),
            'detailFields'=>$l->fields($logbookId,'detail'),
            'bundle'=>$bundle,
            'dateFrom'=>$dateFrom,
            'dateTo'=>$dateTo
        ],'none');
        audit('print_range','logbook',$logbookId,['date_from'=>$dateFrom,'date_to'=>$dateTo,'sessions'=>count($bundle)]);
    }
}
