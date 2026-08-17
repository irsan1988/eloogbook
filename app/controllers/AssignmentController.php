<?php
class AssignmentController extends Controller {
    private AssignmentModel $a;
    private LogbookModel $l;
    public function __construct(){ $this->a=new AssignmentModel(); $this->l=new LogbookModel(); }

    public function index(): void {
        $this->requireAdmin();
        $all=$this->l->all(false); $general=[]; $daily=[];
        foreach($all as $x){ if(is_xray_special_code((string)$x['code'])) $daily[]=$x; else $general[]=$x; }
        usort($daily,fn($a,$b)=>strcmp((string)$a['code'],(string)$b['code']));
        $this->view('assignments/index',[
            'title'=>'Penugasan Petugas',
            'petugas'=>$this->a->petugas(),
            'general'=>$general,
            'daily'=>$daily,
            'generalMap'=>$this->a->generalMap(),
            'dailyMap'=>$this->a->dailyMap(),
        ]);
    }

    public function saveGeneral(string $id): void {
        $this->requireAdmin(); $this->csrf();
        $lb=$this->l->find((int)$id); if(!$lb) abort(404,'Logbook tidak ditemukan.');
        if(is_xray_special_code((string)$lb['code'])) abort(422,'Gunakan penugasan Daily Check untuk template X-Ray.');
        $ids=$_POST['user_ids']??[]; if(!is_array($ids))$ids=[];
        $this->a->saveGeneral((int)$id,$ids,(int)Auth::id());
        audit('assign_petugas','logbook',(int)$id,['user_ids'=>array_map('intval',$ids)]);
        flash('success','Penugasan petugas untuk '.$lb['name'].' diperbarui.'); $this->redirect('assignments');
    }

    public function saveDaily(string $id): void {
        $this->requireAdmin(); $this->csrf();
        $lb=$this->l->find((int)$id); if(!$lb) abort(404,'Daily Check tidak ditemukan.');
        if(!is_xray_special_code((string)$lb['code'])) abort(422,'Penugasan ini khusus Daily Check Harian X-Ray.');
        $uid=(int)($_POST['user_id']??0);
        $this->a->saveDaily((int)$id,$uid?:null,(int)Auth::id());
        audit('assign_daily_petugas','logbook',(int)$id,['user_id'=>$uid?:null]);
        flash('success',$uid?'Petugas Daily Check diperbarui.':'Daily Check sekarang belum memiliki petugas.'); $this->redirect('assignments');
    }
}
