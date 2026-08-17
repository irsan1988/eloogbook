<?php
class LogbookModel extends Model {
    public function all(bool $activeOnly=false): array { $sql='SELECT l.*,u.name creator FROM logbooks l LEFT JOIN users u ON u.id=l.created_by'.($activeOnly?' WHERE l.active=1':'').' ORDER BY l.name'; return $this->db->query($sql)->fetchAll(); }

    public function accessible(bool $activeOnly=false): array {
        $all=$this->all($activeOnly);
        if(!Auth::isPetugas()) return $all;
        $ids=array_flip((new AssignmentModel())->assignedLogbookIds((int)Auth::id()));
        return array_values(array_filter($all,static fn(array $x): bool => isset($ids[(int)$x['id']])));
    }

    public function find(int $id): ?array { $s=$this->db->prepare('SELECT * FROM logbooks WHERE id=?');$s->execute([$id]);$r=$s->fetch();return $r?:null; }
    public function create(array $d): int { $s=$this->db->prepare('INSERT INTO logbooks(code,name,description,print_layout,orientation,active,created_by,created_at) VALUES(?,?,?,?,?,1,?,NOW())');$s->execute([$d['code'],$d['name'],$d['description'],$d['print_layout'],$d['orientation'],Auth::id()]);return (int)$this->db->lastInsertId(); }
    public function update(int $id,array $d): void { $s=$this->db->prepare('UPDATE logbooks SET code=?,name=?,description=?,print_layout=?,orientation=?,active=? WHERE id=?');$s->execute([$d['code'],$d['name'],$d['description'],$d['print_layout'],$d['orientation'],$d['active'],$id]); }
    public function setActive(int $id,bool $active): void { $s=$this->db->prepare('UPDATE logbooks SET active=? WHERE id=?');$s->execute([$active?1:0,$id]); }

    /**
     * Hapus jenis logbook beserta seluruh data turunannya.
     * Urutan ini sengaja eksplisit agar tetap bekerja pada database instalasi lama
     * yang masih memakai FK RESTRICT pada logbook_sessions -> logbooks.
     */
    public function delete(int $id): void {
        $this->db->beginTransaction();
        try {
            $s=$this->db->prepare('DELETE FROM logbook_sessions WHERE logbook_id=?');
            $s->execute([$id]); // session_values, rows, row_values ikut terhapus lewat CASCADE

            $s=$this->db->prepare('DELETE FROM logbook_fields WHERE logbook_id=?');
            $s->execute([$id]);

            $s=$this->db->prepare('DELETE FROM logbooks WHERE id=?');
            $s->execute([$id]);
            if($s->rowCount()===0) throw new RuntimeException('Logbook tidak ditemukan.');

            $this->db->commit();
        } catch(Throwable $e) {
            if($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function fields(int $logbookId, ?string $section=null): array { $sql='SELECT * FROM logbook_fields WHERE logbook_id=?';$p=[$logbookId]; if($section){$sql.=' AND section=?';$p[]=$section;} $sql.=' ORDER BY sort_order,id';$s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll(); }
    public function field(int $id): ?array { $s=$this->db->prepare('SELECT * FROM logbook_fields WHERE id=?');$s->execute([$id]);$r=$s->fetch();return $r?:null; }
    public function addField(int $logbookId,array $d): int { $s=$this->db->prepare('INSERT INTO logbook_fields(logbook_id,section,label,field_key,field_type,options,required,help_text,sort_order,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');$s->execute([$logbookId,$d['section'],$d['label'],$d['field_key'],$d['field_type'],$d['options'],$d['required'],$d['help_text'],$d['sort_order']]);return (int)$this->db->lastInsertId(); }
    public function updateField(int $id,array $d): void { $s=$this->db->prepare('UPDATE logbook_fields SET section=?,label=?,field_key=?,field_type=?,options=?,required=?,help_text=?,sort_order=? WHERE id=?');$s->execute([$d['section'],$d['label'],$d['field_key'],$d['field_type'],$d['options'],$d['required'],$d['help_text'],$d['sort_order'],$id]); }
    public function deleteField(int $id): void { $s=$this->db->prepare('DELETE FROM logbook_fields WHERE id=?');$s->execute([$id]); }
}
