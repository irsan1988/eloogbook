<?php
class UserModel extends Model {
    public function findByUsername(string $u): ?array { $s=$this->db->prepare('SELECT * FROM users WHERE username=? AND active=1 LIMIT 1');$s->execute([$u]);$r=$s->fetch();return $r?:null; }
    public function find(int $id): ?array { $s=$this->db->prepare('SELECT id,name,username,role,active,qr_issued_at,created_at FROM users WHERE id=? LIMIT 1');$s->execute([$id]);$r=$s->fetch();return $r?:null; }
    public function findByQrToken(string $token): ?array {
        $hash=hash('sha256',$token);
        $s=$this->db->prepare("SELECT * FROM users WHERE qr_token_hash=? AND active=1 AND role='petugas' LIMIT 1");
        $s->execute([$hash]); $r=$s->fetch(); return $r?:null;
    }
    public function all(): array { return $this->db->query('SELECT id,name,username,role,active,qr_issued_at,created_at FROM users ORDER BY name')->fetchAll(); }
    public function create(array $d): int { $s=$this->db->prepare('INSERT INTO users(name,username,password,role,active,created_at) VALUES(?,?,?,?,1,NOW())');$s->execute([$d['name'],$d['username'],password_hash($d['password'],PASSWORD_DEFAULT),$d['role']]);return (int)$this->db->lastInsertId(); }
    public function update(int $id,array $d): void { if(!empty($d['password'])){$s=$this->db->prepare('UPDATE users SET name=?,username=?,role=?,active=?,password=? WHERE id=?');$s->execute([$d['name'],$d['username'],$d['role'],$d['active'],password_hash($d['password'],PASSWORD_DEFAULT),$id]);}else{$s=$this->db->prepare('UPDATE users SET name=?,username=?,role=?,active=? WHERE id=?');$s->execute([$d['name'],$d['username'],$d['role'],$d['active'],$id]);} }
    public function issueQr(int $id,string $token): void {
        $s=$this->db->prepare("UPDATE users SET qr_token_hash=?,qr_issued_at=NOW() WHERE id=? AND role='petugas'");
        $s->execute([hash('sha256',$token),$id]);
        if($s->rowCount()===0) throw new RuntimeException('QR Code login hanya tersedia untuk role Petugas.');
    }
    public function revokeQr(int $id): void { $this->db->prepare('UPDATE users SET qr_token_hash=NULL,qr_issued_at=NULL WHERE id=?')->execute([$id]); }
    public function delete(int $id): void { $s=$this->db->prepare('DELETE FROM users WHERE id=?');$s->execute([$id]); }

    // Alias internal agar instalasi/route versi lama tidak memutus proses update.
    public function findByBarcodeToken(string $token): ?array { return $this->findByQrToken($token); }
    public function issueBarcode(int $id,string $token): void { $this->issueQr($id,$token); }
    public function revokeBarcode(int $id): void { $this->revokeQr($id); }
}
