<?php
class LogbookController extends Controller {
    private LogbookModel $m; public function __construct(){ $this->m=new LogbookModel(); }
    public function index(): void {
        $this->requireLogin();
        $items=array_values(array_filter($this->m->accessible(!Auth::isAdmin()), static fn(array $x): bool => !is_xray_special_code((string)($x['code']??''))));
        $this->view('logbooks/index',['title'=>'Daftar Logbook','items'=>$items]);
    }

    public function dailyCheck(): void {
        $this->requireLogin();
        // Admin tetap melihat template yang nonaktif agar bisa mengaktifkannya kembali.
        // Petugas/Supervisor hanya melihat Daily Check yang aktif.
        $all=$this->m->accessible(!Auth::isAdmin());
        $byCode=[];
        foreach($all as $x){
            $code=(string)($x['code']??'');
            if(is_xray_special_code($code)) $byCode[$code]=$x;
        }
        $order=[
            'XRAY-SINGLE-BAGASI','XRAY-SINGLE-CABIN','XRAY-SINGLE-SSCP','XRAY-SINGLE-CARGO',
            'XRAY-MULTI-BAGASI','XRAY-MULTI-CABIN','XRAY-MULTI-SSCP','XRAY-MULTI-CARGO',
        ];
        $items=[];
        foreach($order as $code) if(isset($byCode[$code])) $items[]=$byCode[$code];
        $this->view('logbooks/daily_check',['title'=>'DIALY CHECK HARIAN','items'=>$items,'dailyAssignments'=>(new AssignmentModel())->dailyMap()]);
    }
    public function toggleDailyCheck(string $id): void {
        $this->requireAdmin();
        $this->csrf();
        $item=$this->m->find((int)$id);
        if(!$item) abort(404,'Daily Check tidak ditemukan.');
        if(!is_xray_special_code((string)($item['code']??''))) abort(422,'Fitur aktif/nonaktif ini khusus Daily Check Harian X-Ray.');
        $active=(int)($_POST['active']??0)===1;
        $this->m->setActive((int)$id,$active);
        audit($active?'activate':'deactivate','daily_check',(int)$id,['code'=>$item['code'],'name'=>$item['name']]);
        flash('success',$active?'Daily Check X-Ray berhasil diaktifkan.':'Daily Check X-Ray dinonaktifkan. Riwayat dan hasil cetak tetap tersimpan, tetapi pengisian baru dan notifikasi wajib harian dihentikan.');
        $this->redirect('daily-check');
    }
    public function create(): void { $this->requireAdmin();$this->view('logbooks/form',['title'=>'Tambah Logbook','item'=>null]); }
    public function store(): void { $this->requireAdmin();$this->csrf();$d=$this->payload();if($d['name']===''||$d['code']==='')abort(422,'Nama dan kode logbook wajib diisi.');$id=$this->m->create($d);audit('create','logbook',$id,$d);flash('success','Logbook berhasil dibuat. Tambahkan rincian kolom.');$this->redirect('logbooks/'.$id.'/fields'); }
    public function edit(string $id): void { $this->requireAdmin();$item=$this->m->find((int)$id);if(!$item)abort(404,'Logbook tidak ditemukan.');$this->view('logbooks/form',['title'=>'Edit Logbook','item'=>$item]); }
    public function update(string $id): void { $this->requireAdmin();$this->csrf();$item=$this->m->find((int)$id);if(!$item)abort(404,'Logbook tidak ditemukan.');if(is_xray_special_code((string)($item['code']??'')))abort(422,'Template Daily Check X-Ray adalah template tetap. Data pengujian tetap dapat diedit dari menu Data Logbook, tetapi nama, kode, struktur, layout dan orientasi template tidak dapat diubah.');$d=$this->payload();$this->m->update((int)$id,$d);audit('update','logbook',(int)$id,$d);flash('success','Logbook diperbarui.');$this->redirect('logbooks'); }
    public function delete(string $id): void {
        $this->requireAdmin();
        $this->csrf();
        $item=$this->m->find((int)$id);
        if(!$item) abort(404,'Logbook tidak ditemukan.');

        $isDaily=is_xray_special_code((string)($item['code']??''));
        try{
            $meta=['code'=>$item['code'],'name'=>$item['name'],'daily_check'=>$isDaily];
            $this->m->delete((int)$id);
            audit('delete',$isDaily?'daily_check':'logbook',(int)$id,$meta);
            flash('success',$isDaily
                ? 'Daily Check X-Ray dan seluruh data pengisian, penugasan, serta riwayat sesi di dalamnya berhasil dihapus permanen.'
                : 'Jenis logbook dan seluruh data di dalamnya berhasil dihapus.');
        }catch(Throwable $e){
            flash('error','Gagal menghapus '.($isDaily?'Daily Check X-Ray':'logbook').': '.$e->getMessage());
        }
        $this->redirect($isDaily?'daily-check':'logbooks');
    }
    public function fields(string $id): void { $this->requireAdmin();$item=$this->m->find((int)$id);if(!$item)abort(404,'Logbook tidak ditemukan.');$this->view('logbooks/fields',['title'=>'Rincian Logbook','item'=>$item,'fields'=>$this->m->fields((int)$id)]); }
    public function storeField(string $id): void { $this->requireAdmin();$this->csrf();$item=$this->m->find((int)$id);if(!$item)abort(404,'Logbook tidak ditemukan.');if(is_xray_special_code((string)($item['code']??'')))abort(422,'Rincian template X-Ray khusus dikunci agar posisi checkbox dan hasil cetak tidak rusak.');$d=$this->fieldPayload();$fid=$this->m->addField((int)$id,$d);audit('create','logbook_field',$fid,$d);flash('success','Kolom ditambahkan.');$this->redirect('logbooks/'.$id.'/fields'); }
    public function updateField(string $id): void { $this->requireAdmin();$this->csrf();$f=$this->m->field((int)$id);if(!$f)abort(404,'Kolom tidak ditemukan.');$item=$this->m->find((int)$f['logbook_id']);if(is_xray_special_code((string)($item['code']??'')))abort(422,'Rincian template X-Ray khusus dikunci agar posisi checkbox dan hasil cetak tidak rusak.');$d=$this->fieldPayload();$this->m->updateField((int)$id,$d);audit('update','logbook_field',(int)$id,$d);flash('success','Kolom diperbarui.');$this->redirect('logbooks/'.$f['logbook_id'].'/fields'); }
    public function deleteField(string $id): void { $this->requireAdmin();$this->csrf();$f=$this->m->field((int)$id);if(!$f)abort(404,'Kolom tidak ditemukan.');$item=$this->m->find((int)$f['logbook_id']);if(is_xray_special_code((string)($item['code']??'')))abort(422,'Rincian template X-Ray khusus dikunci agar posisi checkbox dan hasil cetak tidak rusak.');try{$this->m->deleteField((int)$id);audit('delete','logbook_field',(int)$id);flash('success','Kolom dihapus.');}catch(Throwable$e){flash('error','Kolom tidak dapat dihapus karena sudah memiliki nilai tersimpan.');}$this->redirect('logbooks/'.$f['logbook_id'].'/fields'); }
    private function payload(): array { return ['code'=>strtoupper(trim($_POST['code']??'')),'name'=>trim($_POST['name']??''),'description'=>trim($_POST['description']??''),'print_layout'=>in_array($_POST['print_layout']??'table',['table','form'],true)?$_POST['print_layout']:'table','orientation'=>in_array($_POST['orientation']??'landscape',['portrait','landscape'],true)?$_POST['orientation']:'landscape','active'=>(int)($_POST['active']??1)]; }
    private function fieldPayload(): array { $label=trim($_POST['label']??'');$key=trim($_POST['field_key']??'');if($key==='')$key=strtolower(preg_replace('/[^A-Za-z0-9]+/','_',iconv('UTF-8','ASCII//TRANSLIT',$label)?:$label));$types=['text','textarea','number','date','time','datetime-local','select','checkbox'];$type=in_array($_POST['field_type']??'text',$types,true)?$_POST['field_type']:'text';return ['section'=>in_array($_POST['section']??'detail',['header','detail'],true)?$_POST['section']:'detail','label'=>$label,'field_key'=>$key,'field_type'=>$type,'options'=>trim($_POST['options']??''),'required'=>(int)($_POST['required']??0),'help_text'=>trim($_POST['help_text']??''),'sort_order'=>(int)($_POST['sort_order']??0)]; }
}
