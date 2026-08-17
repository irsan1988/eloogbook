<?php
class XrayMasterController extends Controller {
    private XrayMasterModel $m;
    public function __construct(){ $this->m=new XrayMasterModel(); }

    public function index(): void {
        $this->requireAdmin();
        $this->view('xray_master/index',[
            'title'=>'Master Data DIALY CHECK X-Ray',
            'types'=>$this->m->types(),
            'items'=>$this->m->allGrouped(false),
        ]);
    }

    public function store(string $type): void {
        $this->requireAdmin(); $this->csrf();
        $d=$this->payload();
        try{
            $id=$this->m->create($type,$d);
            audit('create','xray_master_'.$type,$id,['value'=>$d['value']]);
            flash('success',$this->m->label($type).' ditambahkan ke dropdown Daily Check X-Ray.');
        }catch(Throwable $e){
            flash('error','Data gagal ditambahkan. Pastikan nilai belum ada pada kategori yang sama.');
        }
        $this->redirect('xray-master#'.$type);
    }

    public function update(string $type,string $id): void {
        $this->requireAdmin(); $this->csrf();
        if(!$this->m->find($type,(int)$id)) abort(404,'Master data tidak ditemukan.');
        $d=$this->payload();
        try{
            $this->m->update($type,(int)$id,$d);
            audit('update','xray_master_'.$type,(int)$id,['value'=>$d['value'],'active'=>$d['active']]);
            flash('success','Master data X-Ray diperbarui.');
        }catch(Throwable $e){
            flash('error','Data gagal diperbarui. Pastikan nilai tidak duplikat.');
        }
        $this->redirect('xray-master#'.$type);
    }

    public function delete(string $type,string $id): void {
        $this->requireAdmin(); $this->csrf();
        $row=$this->m->find($type,(int)$id);
        if(!$row) abort(404,'Master data tidak ditemukan.');
        $this->m->delete($type,(int)$id);
        audit('delete','xray_master_'.$type,(int)$id,['value'=>$row['value']]);
        flash('success','Pilihan dihapus dari master data. Data logbook lama tidak ikut terhapus.');
        $this->redirect('xray-master#'.$type);
    }

    private function payload(): array {
        $value=trim((string)($_POST['value']??''));
        if($value==='') abort(422,'Nilai master data wajib diisi.');
        if(mb_strlen($value)>191) abort(422,'Nilai master data maksimal 191 karakter.');
        return [
            'value'=>$value,
            'active'=>(int)($_POST['active']??1)===1?1:0,
            'sort_order'=>(int)($_POST['sort_order']??0),
        ];
    }
}
