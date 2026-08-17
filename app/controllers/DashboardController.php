<?php
class DashboardController extends Controller {
    public function index(): void {
        $this->requireLogin();
        $db=Database::conn(); $stats=[];
        if(Auth::isPetugas()){
            $ids=(new AssignmentModel())->assignedLogbookIds((int)Auth::id());
            $stats['logbooks']=count($ids);
            if($ids){
                $ph=implode(',',array_fill(0,count($ids),'?'));
                $q=$db->prepare("SELECT COUNT(*) FROM logbook_sessions WHERE session_date=CURDATE() AND logbook_id IN ($ph)");$q->execute($ids);$stats['today']=(int)$q->fetchColumn();
                $q=$db->prepare("SELECT COUNT(*) FROM logbook_rows r JOIN logbook_sessions s ON s.id=r.session_id WHERE s.logbook_id IN ($ph)");$q->execute($ids);$stats['rows']=(int)$q->fetchColumn();
            }else{$stats['today']=0;$stats['rows']=0;}
        }else{
            $stats['logbooks']=(int)$db->query('SELECT COUNT(*) c FROM logbooks WHERE active=1')->fetch()['c'];
            $stats['today']=(int)$db->query("SELECT COUNT(*) c FROM logbook_sessions WHERE session_date=CURDATE()")->fetch()['c'];
            $stats['rows']=(int)$db->query('SELECT COUNT(*) c FROM logbook_rows')->fetch()['c'];
        }
        $stats['users']=(int)$db->query('SELECT COUNT(*) c FROM users WHERE active=1')->fetch()['c'];
        $recent=(new EntryModel())->sessions([]); $recent=array_slice($recent,0,8);
        $missingToday=Auth::canMonitor()?(new MonitoringModel())->missingTodayCount():0;
        $this->view('dashboard/index',['title'=>'Dashboard','stats'=>$stats,'recent'=>$recent,'missingToday'=>$missingToday]);
    }
}
