<?php
class MonitoringModel extends Model {
    public function missingReport(string $dateFrom,string $dateTo): array {
        $from=new DateTimeImmutable($dateFrom);
        $to=new DateTimeImmutable($dateTo);
        if($to<$from) [$from,$to]=[$to,$from];
        if($from->diff($to)->days>92) $from=$to->modify('-92 days');

        $logs=$this->db->query("SELECT id,code,name,DATE(created_at) created_date FROM logbooks WHERE active=1 ORDER BY name")->fetchAll();
        $assigned=[];
        $qAssign=$this->db->query("SELECT a.logbook_id,GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') names FROM logbook_petugas_assignments a JOIN users u ON u.id=a.user_id WHERE u.active=1 AND u.role='petugas' GROUP BY a.logbook_id");
        foreach($qAssign->fetchAll() as $a) $assigned[(int)$a['logbook_id']]=(string)$a['names'];
        $qAssign=$this->db->query("SELECT a.logbook_id,u.name names FROM daily_check_petugas_assignments a JOIN users u ON u.id=a.user_id WHERE u.active=1 AND u.role='petugas'");
        foreach($qAssign->fetchAll() as $a) $assigned[(int)$a['logbook_id']]=(string)$a['names'];
        foreach($logs as &$l) $l['assigned_petugas']=$assigned[(int)$l['id']]??'';
        unset($l);
        $q=$this->db->prepare("SELECT s.session_date,s.logbook_id,COUNT(r.id) row_count
            FROM logbook_sessions s
            LEFT JOIN logbook_rows r ON r.session_id=s.id
            WHERE s.session_date BETWEEN ? AND ?
            GROUP BY s.session_date,s.logbook_id");
        $q->execute([$from->format('Y-m-d'),$to->format('Y-m-d')]);
        $filled=[];
        foreach($q->fetchAll() as $x) if((int)$x['row_count']>0) $filled[$x['session_date']][(int)$x['logbook_id']]=true;

        $out=[];
        for($d=$from;$d<=$to;$d=$d->modify('+1 day')){
            $date=$d->format('Y-m-d');
            $missing=[];
            foreach($logs as $l){
                if(($l['created_date']??'')>$date) continue;
                if(empty($filled[$date][(int)$l['id']])) $missing[]=$l;
            }
            if($missing) $out[]=['date'=>$date,'missing'=>$missing,'count'=>count($missing)];
        }
        return array_reverse($out);
    }

    public function missingTodayCount(): int {
        $r=$this->missingReport(date('Y-m-d'),date('Y-m-d'));
        return $r ? (int)$r[0]['count'] : 0;
    }
}
