<?php
class AssignmentModel extends Model {
    public function petugas(): array {
        return $this->db->query("SELECT id,name,username,active FROM users WHERE role='petugas' AND active=1 ORDER BY name")->fetchAll();
    }

    public function assignedLogbookIds(int $userId): array {
        $ids=[];
        $q=$this->db->prepare('SELECT logbook_id FROM logbook_petugas_assignments WHERE user_id=?');
        $q->execute([$userId]);
        foreach($q->fetchAll() as $r) $ids[(int)$r['logbook_id']]=true;
        $q=$this->db->prepare('SELECT logbook_id FROM daily_check_petugas_assignments WHERE user_id=?');
        $q->execute([$userId]);
        foreach($q->fetchAll() as $r) $ids[(int)$r['logbook_id']]=true;
        return array_keys($ids);
    }

    public function isAssigned(int $userId,int $logbookId,bool $daily): bool {
        $table=$daily?'daily_check_petugas_assignments':'logbook_petugas_assignments';
        $q=$this->db->prepare("SELECT 1 FROM {$table} WHERE logbook_id=? AND user_id=? LIMIT 1");
        $q->execute([$logbookId,$userId]);
        return (bool)$q->fetchColumn();
    }

    public function generalMap(): array {
        $rows=$this->db->query("SELECT a.logbook_id,a.user_id,u.name,u.username FROM logbook_petugas_assignments a JOIN users u ON u.id=a.user_id WHERE u.role='petugas' ORDER BY u.name")->fetchAll();
        $map=[]; foreach($rows as $r) $map[(int)$r['logbook_id']][]=$r; return $map;
    }

    public function dailyMap(): array {
        $rows=$this->db->query("SELECT a.logbook_id,a.user_id,u.name,u.username FROM daily_check_petugas_assignments a JOIN users u ON u.id=a.user_id WHERE u.role='petugas'")->fetchAll();
        $map=[]; foreach($rows as $r) $map[(int)$r['logbook_id']]=$r; return $map;
    }

    public function saveGeneral(int $logbookId,array $userIds,int $adminId): void {
        $valid=[];
        if($userIds){
            $requested=array_values(array_unique(array_filter(array_map('intval',$userIds),static fn(int $x): bool=>$x>0)));
            $place=implode(',',array_fill(0,count($requested),'?'));
            $q=$this->db->prepare("SELECT id FROM users WHERE role='petugas' AND active=1 AND id IN ($place)");
            $q->execute($requested);
            foreach($q->fetchAll() as $r) $valid[]=(int)$r['id'];
        }
        $this->db->beginTransaction();
        try{
            $this->db->prepare('DELETE FROM logbook_petugas_assignments WHERE logbook_id=?')->execute([$logbookId]);
            $ins=$this->db->prepare('INSERT INTO logbook_petugas_assignments(logbook_id,user_id,assigned_by,created_at) VALUES(?,?,?,NOW())');
            foreach($valid as $uid) $ins->execute([$logbookId,$uid,$adminId]);
            $this->db->commit();
        }catch(Throwable $e){ if($this->db->inTransaction())$this->db->rollBack(); throw $e; }
    }

    public function saveDaily(int $logbookId,?int $userId,int $adminId): void {
        if($userId){
            $q=$this->db->prepare("SELECT id FROM users WHERE id=? AND role='petugas' AND active=1 LIMIT 1");
            $q->execute([$userId]); if(!$q->fetchColumn()) throw new RuntimeException('Petugas tidak aktif atau tidak valid.');
        }
        $this->db->beginTransaction();
        try{
            $this->db->prepare('DELETE FROM daily_check_petugas_assignments WHERE logbook_id=?')->execute([$logbookId]);
            if($userId){
                $q=$this->db->prepare('INSERT INTO daily_check_petugas_assignments(logbook_id,user_id,assigned_by,created_at,updated_at) VALUES(?,?,?,NOW(),NOW())');
                $q->execute([$logbookId,$userId,$adminId]);
            }
            $this->db->commit();
        }catch(Throwable $e){ if($this->db->inTransaction())$this->db->rollBack(); throw $e; }
    }

    public function removeUser(int $userId): void {
        $this->db->prepare('DELETE FROM logbook_petugas_assignments WHERE user_id=?')->execute([$userId]);
        $this->db->prepare('DELETE FROM daily_check_petugas_assignments WHERE user_id=?')->execute([$userId]);
    }
}
