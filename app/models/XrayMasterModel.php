<?php
class XrayMasterModel extends Model {
    private const TYPES = [
        'operator' => ['table'=>'xray_master_operators','label'=>'Nama Operator Penerbangan'],
        'lokasi' => ['table'=>'xray_master_locations','label'=>'Lokasi Penempatan / Gedung'],
        'mesin' => ['table'=>'xray_master_machines','label'=>'Merk / Tipe / Nomor Seri'],
        'sertifikat' => ['table'=>'xray_master_certificates','label'=>'Nomor dan Tanggal Sertifikat'],
    ];

    public function types(): array { return self::TYPES; }

    public function allGrouped(bool $activeOnly=false): array {
        $out=[];
        foreach(self::TYPES as $type=>$meta) $out[$type]=$this->all($type,$activeOnly);
        return $out;
    }

    public function all(string $type, bool $activeOnly=false): array {
        $table=$this->table($type);
        $sql="SELECT * FROM {$table}".($activeOnly?' WHERE active=1':'').' ORDER BY sort_order ASC, value ASC, id ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function activeValues(string $type): array {
        return array_values(array_map(static fn(array $r): string => (string)$r['value'],$this->all($type,true)));
    }

    public function optionsByFieldKey(): array {
        $out=[];
        foreach(array_keys(self::TYPES) as $type) $out[$type]=$this->activeValues($type);
        return $out;
    }

    public function find(string $type,int $id): ?array {
        $table=$this->table($type);
        $s=$this->db->prepare("SELECT * FROM {$table} WHERE id=? LIMIT 1");
        $s->execute([$id]);
        $r=$s->fetch();
        return $r?:null;
    }

    public function create(string $type,array $d): int {
        $table=$this->table($type);
        $s=$this->db->prepare("INSERT INTO {$table}(value,active,sort_order,created_at,updated_at) VALUES(?,?,?,?,NOW())");
        $s->execute([$d['value'],$d['active'],$d['sort_order'],date('Y-m-d H:i:s')]);
        return (int)$this->db->lastInsertId();
    }

    public function update(string $type,int $id,array $d): void {
        $table=$this->table($type);
        $s=$this->db->prepare("UPDATE {$table} SET value=?,active=?,sort_order=?,updated_at=NOW() WHERE id=?");
        $s->execute([$d['value'],$d['active'],$d['sort_order'],$id]);
    }

    public function delete(string $type,int $id): void {
        $table=$this->table($type);
        $s=$this->db->prepare("DELETE FROM {$table} WHERE id=?");
        $s->execute([$id]);
    }

    public function label(string $type): string {
        $this->assertType($type);
        return self::TYPES[$type]['label'];
    }

    private function table(string $type): string {
        $this->assertType($type);
        return self::TYPES[$type]['table'];
    }

    private function assertType(string $type): void {
        if(!isset(self::TYPES[$type])) abort(404,'Jenis master data X-Ray tidak ditemukan.');
    }
}
