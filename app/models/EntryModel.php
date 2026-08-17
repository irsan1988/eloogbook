<?php
class EntryModel extends Model {
    public function sessions(array $filter=[]): array {
        $sql = 'SELECT s.*,l.name logbook_name,l.code,u.name creator,
                EXISTS(SELECT 1 FROM hidden_logbook_sessions h WHERE h.session_id=s.id AND h.user_id=?) AS hidden_for_user
                FROM logbook_sessions s
                JOIN logbooks l ON l.id=s.logbook_id
                LEFT JOIN users u ON u.id=s.created_by
                WHERE 1=1';
        $p = [Auth::id() ?? 0];
        if (!empty($filter['logbook_id'])) { $sql .= ' AND s.logbook_id=?'; $p[] = $filter['logbook_id']; }
        if (!empty($filter['date_from']))  { $sql .= ' AND s.session_date>=?'; $p[] = $filter['date_from']; }
        if (!empty($filter['date_to']))    { $sql .= ' AND s.session_date<=?'; $p[] = $filter['date_to']; }
        if (Auth::isPetugas()) {
            $ids=(new AssignmentModel())->assignedLogbookIds((int)Auth::id());
            if(!$ids){ $sql .= ' AND 1=0'; }
            else { $sql .= ' AND s.logbook_id IN ('.implode(',',array_fill(0,count($ids),'?')).')'; foreach($ids as $id)$p[]=(int)$id; }
        }
        if (Auth::isPetugas() && empty($filter['show_hidden'])) {
            $sql .= ' AND NOT EXISTS(SELECT 1 FROM hidden_logbook_sessions hx WHERE hx.session_id=s.id AND hx.user_id=?)';
            $p[] = Auth::id();
        }
        if (Auth::isPetugas() && !empty($filter['show_hidden'])) {
            $sql .= ' AND EXISTS(SELECT 1 FROM hidden_logbook_sessions hx WHERE hx.session_id=s.id AND hx.user_id=?)';
            $p[] = Auth::id();
        }
        $sql .= ' ORDER BY s.session_date DESC,s.id DESC LIMIT 500';
        $s = $this->db->prepare($sql); $s->execute($p); return $s->fetchAll();
    }

    public function sessionsForRange(int $logbookId, string $dateFrom, string $dateTo): array {
        $s=$this->db->prepare('SELECT s.*,l.name logbook_name,l.code,l.print_layout,l.orientation,l.description,u.name creator
            FROM logbook_sessions s
            JOIN logbooks l ON l.id=s.logbook_id
            LEFT JOIN users u ON u.id=s.created_by
            WHERE s.logbook_id=? AND s.session_date BETWEEN ? AND ?
            ORDER BY s.session_date ASC,s.shift ASC,s.id ASC');
        $s->execute([$logbookId,$dateFrom,$dateTo]);
        return $s->fetchAll();
    }

    public function session(int $id): ?array {
        $s = $this->db->prepare('SELECT s.*,l.name logbook_name,l.code,l.print_layout,l.orientation,l.description,l.active logbook_active,u.name creator FROM logbook_sessions s JOIN logbooks l ON l.id=s.logbook_id LEFT JOIN users u ON u.id=s.created_by WHERE s.id=?');
        $s->execute([$id]); $r = $s->fetch(); return $r ?: null;
    }

    public function findExistingSession(int $logbookId, string $date, string $shift='', int $excludeId=0): ?array {
        $sql = "SELECT * FROM logbook_sessions WHERE logbook_id=? AND session_date=? AND LOWER(TRIM(COALESCE(shift,'')))=LOWER(TRIM(?))";
        $p = [$logbookId, $date, trim($shift)];
        if ($excludeId > 0) { $sql .= ' AND id<>?'; $p[] = $excludeId; }
        $sql .= ' ORDER BY id ASC LIMIT 1';
        $s = $this->db->prepare($sql); $s->execute($p); $r = $s->fetch(); return $r ?: null;
    }

    /** Daily Check X-Ray: satu sesi per jenis logbook per tanggal, tanpa membedakan shift. */
    public function findExistingDailySession(int $logbookId, string $date, int $excludeId=0): ?array {
        $sql='SELECT * FROM logbook_sessions WHERE logbook_id=? AND session_date=?';
        $p=[$logbookId,$date];
        if($excludeId>0){ $sql.=' AND id<>?'; $p[]=$excludeId; }
        $sql.=' ORDER BY id ASC LIMIT 1';
        $q=$this->db->prepare($sql); $q->execute($p); $r=$q->fetch(); return $r?:null;
    }

    public function createSession(int $logbookId, string $date, string $shift='', ?string $dailyOnceKey=null): int {
        $s = $this->db->prepare('INSERT INTO logbook_sessions(logbook_id,session_date,shift,daily_once_key,created_by,created_at,updated_at) VALUES(?,?,?,?,?,NOW(),NOW())');
        $s->execute([$logbookId, $date, trim($shift), $dailyOnceKey, Auth::id()]);
        return (int)$this->db->lastInsertId();
    }

    public function markDailySessionKey(int $sessionId,int $logbookId,string $date): void {
        $key='XRAY-DAY-'.$logbookId.'-'.$date;
        try { $this->db->prepare("UPDATE logbook_sessions SET daily_once_key=? WHERE id=? AND (daily_once_key IS NULL OR daily_once_key='')")->execute([$key,$sessionId]); }
        catch(PDOException $e){ if(($e->errorInfo[0]??'')!=='23000') throw $e; }
    }

    public function saveSessionValues(int $sessionId, array $values): void {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare('DELETE FROM logbook_session_values WHERE session_id=?'); $del->execute([$sessionId]);
            $ins = $this->db->prepare('INSERT INTO logbook_session_values(session_id,field_id,value) VALUES(?,?,?)');
            foreach ($values as $fid=>$val) $ins->execute([$sessionId,(int)$fid,is_array($val)?json_encode($val):$val]);
            $this->db->prepare('UPDATE logbook_sessions SET updated_at=NOW() WHERE id=?')->execute([$sessionId]);
            $this->db->commit();
        } catch(Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function sessionValues(int $sessionId): array {
        $s = $this->db->prepare('SELECT field_id,value FROM logbook_session_values WHERE session_id=?');
        $s->execute([$sessionId]); $o=[]; foreach($s->fetchAll() as $r) $o[(int)$r['field_id']]=$r['value']; return $o;
    }

    public function nextSequence(int $sessionId): int {
        $s = $this->db->prepare('SELECT COALESCE(MAX(sequence_no),0)+1 n FROM logbook_rows WHERE session_id=?');
        $s->execute([$sessionId]); return (int)$s->fetch()['n'];
    }

    public function createRow(int $sessionId): int {
        for ($attempt=0; $attempt<3; $attempt++) {
            $n = $this->nextSequence($sessionId);
            try {
                $s = $this->db->prepare('INSERT INTO logbook_rows(session_id,sequence_no,created_by,created_at,updated_at) VALUES(?,?,?,NOW(),NOW())');
                $s->execute([$sessionId,$n,Auth::id()]);
                return (int)$this->db->lastInsertId();
            } catch (PDOException $e) {
                if (($e->errorInfo[0] ?? '') !== '23000' || $attempt === 2) throw $e;
                usleep(50000);
            }
        }
        throw new RuntimeException('Gagal menentukan nomor baris berikutnya.');
    }

    /** Daily Check X-Ray hanya boleh memiliki satu lembar/baris. */
    public function createSingleRow(int $sessionId): array {
        $existing=$this->db->prepare('SELECT id FROM logbook_rows WHERE session_id=? ORDER BY id ASC LIMIT 1');
        $existing->execute([$sessionId]);
        $id=(int)($existing->fetchColumn()?:0);
        if($id>0) return ['id'=>$id,'created'=>false];

        $key='XRAY-SESSION-'.$sessionId;
        try {
            $s=$this->db->prepare('INSERT INTO logbook_rows(session_id,sequence_no,single_entry_key,created_by,created_at,updated_at) VALUES(?,1,?,?,NOW(),NOW())');
            $s->execute([$sessionId,$key,Auth::id()]);
            return ['id'=>(int)$this->db->lastInsertId(),'created'=>true];
        } catch(PDOException $e){
            if(($e->errorInfo[0]??'')!=='23000') throw $e;
            $existing->execute([$sessionId]);
            $id=(int)($existing->fetchColumn()?:0);
            if($id>0) return ['id'=>$id,'created'=>false];
            throw $e;
        }
    }

    public function saveRowValues(int $rowId,array $values): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('DELETE FROM logbook_row_values WHERE row_id=?')->execute([$rowId]);
            $i = $this->db->prepare('INSERT INTO logbook_row_values(row_id,field_id,value) VALUES(?,?,?)');
            foreach($values as $fid=>$val) $i->execute([$rowId,(int)$fid,is_array($val)?json_encode($val):$val]);
            $this->db->prepare('UPDATE logbook_rows SET updated_at=NOW() WHERE id=?')->execute([$rowId]);
            $this->db->commit();
        } catch(Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function rows(int $sessionId): array {
        $s = $this->db->prepare('SELECT r.*,u.name creator FROM logbook_rows r LEFT JOIN users u ON u.id=r.created_by WHERE r.session_id=? ORDER BY r.sequence_no,r.id');
        $s->execute([$sessionId]); $rows=$s->fetchAll();
        $vs = $this->db->prepare('SELECT rv.row_id,rv.field_id,rv.value FROM logbook_row_values rv JOIN logbook_rows r ON r.id=rv.row_id WHERE r.session_id=?');
        $vs->execute([$sessionId]); $map=[];
        foreach($vs->fetchAll() as $v) $map[(int)$v['row_id']][(int)$v['field_id']]=$v['value'];
        foreach($rows as &$r) $r['values']=$map[(int)$r['id']]??[];
        return $rows;
    }

    public function row(int $id): ?array {
        $s = $this->db->prepare('SELECT * FROM logbook_rows WHERE id=?'); $s->execute([$id]); $r=$s->fetch();
        if(!$r) return null;
        $v=$this->db->prepare('SELECT field_id,value FROM logbook_row_values WHERE row_id=?'); $v->execute([$id]);
        $r['values']=[]; foreach($v->fetchAll() as $x) $r['values'][(int)$x['field_id']]=$x['value'];
        return $r;
    }

    public function deleteRow(int $id): void { $this->db->prepare('DELETE FROM logbook_rows WHERE id=?')->execute([$id]); }

    public function updateSessionMeta(int $id,string $date,string $shift='', ?string $dailyOnceKey=null): void {
        $this->db->prepare('UPDATE logbook_sessions SET session_date=?,shift=?,daily_once_key=?,updated_at=NOW() WHERE id=?')->execute([$date,trim($shift),$dailyOnceKey,$id]);
    }

    public function deleteSession(int $id): void { $this->db->prepare('DELETE FROM logbook_sessions WHERE id=?')->execute([$id]); }

    public function hideSessionForUser(int $sessionId, int $userId): void {
        $s=$this->db->prepare('INSERT IGNORE INTO hidden_logbook_sessions(user_id,session_id,hidden_at) VALUES(?,?,NOW())');
        $s->execute([$userId,$sessionId]);
    }

    public function unhideSessionForUser(int $sessionId, int $userId): void {
        $s=$this->db->prepare('DELETE FROM hidden_logbook_sessions WHERE user_id=? AND session_id=?');
        $s->execute([$userId,$sessionId]);
    }
}
