<?php
class EntryController extends Controller {
    private EntryModel $e;
    private LogbookModel $l;
    private XrayMasterModel $xm;

    public function __construct(){
        $this->e = new EntryModel();
        $this->l = new LogbookModel();
        $this->xm = new XrayMasterModel();
    }

    public function index(): void {
        $this->requireLogin();
        $f = [
            'logbook_id' => (int)($_GET['logbook_id'] ?? 0),
            'date_from'  => $_GET['date_from'] ?? '',
            'date_to'    => $_GET['date_to'] ?? '',
            'show_hidden'=> (int)($_GET['show_hidden'] ?? 0)
        ];
        $this->view('entries/index', [
            'title'    => 'Data Logbook',
            'sessions' => $this->e->sessions($f),
            'logbooks' => $this->l->accessible(!Auth::isAdmin()),
            'filter'   => $f
        ]);
    }

    public function createSession(string $logbookId): void {
        $this->requireOperator();
        $lb = $this->l->find((int)$logbookId);
        if (!$lb || !(int)$lb['active']) abort(404, 'Logbook tidak aktif atau tidak ditemukan.');
        $this->requireAssignedLogbook($lb);
        $this->view('entries/session_form', [
            'title'        => 'Buka / Mulai '.$lb['name'],
            'logbook'      => $lb,
            'fields'       => $this->headerFields($lb),
            'session'      => null,
            'values'       => [],
            'detailFields' => is_xray_special_code((string)($lb['code'] ?? '')) ? $this->l->fields((int)$lb['id'], 'detail') : [],
            'detailValues' => []
        ]);
    }

    public function storeSession(string $logbookId): void {
        $this->requireOperator();
        $this->csrf();
        $lb = $this->l->find((int)$logbookId);
        if (!$lb || !(int)$lb['active']) abort(404, 'Logbook tidak aktif atau tidak ditemukan.');
        $this->requireAssignedLogbook($lb);

        $date = $_POST['session_date'] ?? date('Y-m-d');
        $isXray = is_xray_special_code((string)($lb['code'] ?? ''));
        $shift = $isXray ? '' : trim($_POST['shift'] ?? '');

        // Daily Check X-Ray hanya satu kali per jenis X-Ray per tanggal.
        // Logbook umum tetap memakai kunci jenis + tanggal + dinas/regu/shift.
        $existing = $isXray
            ? $this->e->findExistingDailySession((int)$logbookId, $date)
            : $this->e->findExistingSession((int)$logbookId, $date, $shift);

        if ($existing) {
            if ($isXray) {
                $this->e->markDailySessionKey((int)$existing['id'], (int)$logbookId, $date);
                audit('open_existing_daily', 'session', (int)$existing['id'], ['logbook_id'=>(int)$logbookId,'date'=>$date]);
                flash('success', 'Daily Check X-Ray tanggal tersebut sudah tersedia. Satu jenis Daily Check hanya boleh satu lembar per hari. Silakan lihat atau edit data yang sudah ada.');
            } else {
                $next = $this->e->nextSequence((int)$existing['id']);
                audit('open_existing', 'session', (int)$existing['id'], ['logbook_id'=>(int)$logbookId,'date'=>$date,'shift'=>$shift,'next_sequence'=>$next]);
                flash('success', 'Sesi pada tanggal dan dinas/regu/shift tersebut sudah ada. Isian berikutnya akan menjadi baris No. '.$next.'.');
            }
            $this->redirect('entries/session/'.$existing['id']);
        }

        $fields = $this->headerFields($lb);
        $vals = $this->collect($fields, $_POST['f'] ?? []);
        $detailFields = $isXray ? $this->l->fields((int)$logbookId, 'detail') : [];
        $detailVals = $isXray ? $this->collect($detailFields, $_POST['f'] ?? []) : [];
        $dailyKey = $isXray ? 'XRAY-DAY-'.(int)$logbookId.'-'.$date : null;

        try {
            $sid = $this->e->createSession((int)$logbookId, $date, $shift, $dailyKey);
        } catch (PDOException $ex) {
            if (($ex->errorInfo[0] ?? '') === '23000') {
                $existing = $isXray
                    ? $this->e->findExistingDailySession((int)$logbookId, $date)
                    : $this->e->findExistingSession((int)$logbookId, $date, $shift);
                if ($existing) {
                    flash('success', $isXray
                        ? 'Daily Check tanggal ini sudah dibuat oleh petugas lain. Satu hari hanya satu lembar. Data yang sudah ada dibuka.'
                        : 'Sesi sudah dibuat oleh petugas lain. Silakan lanjutkan pada sesi yang sama.');
                    $this->redirect('entries/session/'.$existing['id']);
                }
            }
            throw $ex;
        }

        $this->e->saveSessionValues($sid, $vals);
        if ($isXray) {
            $one = $this->e->createSingleRow($sid);
            $rid = (int)$one['id'];
            $this->e->saveRowValues($rid, $detailVals);
            audit('create_daily', 'row', $rid, ['session_id'=>$sid,'daily_once'=>true,'full_template'=>true]);
        }
        audit('create', 'session', $sid, ['logbook_id'=>(int)$logbookId,'daily_once'=>$isXray]);
        flash('success', $isXray
            ? 'Daily Check X-Ray berhasil disimpan sebagai satu lembar lengkap. Dropdown, checkbox, personel, catatan, dan tanda tangan tersimpan bersama.'
            : 'Sesi logbook dibuat. Silakan isi baris pemeriksaan pertama.');
        $this->redirect('entries/session/'.$sid);
    }

    public function session(string $sessionId): void {
        $this->requireLogin();
        $s = $this->authorizedSession((int)$sessionId);
        $headerValues = $this->e->sessionValues((int)$sessionId);
        $data = [
            'title'        => $s['logbook_name'],
            'session'      => $s,
            'headerFields' => $this->headerFields(
                ['id'=>(int)$s['logbook_id'],'code'=>(string)($s['code']??'')],
                $headerValues
            ),
            'detailFields' => $this->l->fields((int)$s['logbook_id'], 'detail'),
            'headerValues' => $headerValues,
            'rows'         => $this->e->rows((int)$sessionId)
        ];
        $code=$s['code']??''; $view = is_xray_single_code($code) ? 'entries/xray_single_session' : (is_xray_multi_code($code) ? 'entries/xray_multi_session' : 'entries/session');
        $this->view($view, $data);
    }

    public function storeRow(string $sessionId): void {
        $this->requireOperator();
        $this->csrf();
        $s = $this->authorizedSession((int)$sessionId);
        $isXray = is_xray_special_code((string)($s['code'] ?? ''));

        if ($isXray && !(int)($s['logbook_active'] ?? 1)) {
            abort(422, 'Daily Check X-Ray ini sedang dinonaktifkan. Aktifkan kembali dari menu DIALY CHECK HARIAN sebelum melakukan pengisian baru.');
        }

        $fields = $this->l->fields((int)$s['logbook_id'], 'detail');
        $vals = $this->collect($fields, $_POST['f'] ?? []);

        if ($isXray) {
            $currentHeader = $this->e->sessionValues((int)$sessionId);
            $headerFields = $this->headerFields(
                ['id'=>(int)$s['logbook_id'],'code'=>(string)($s['code']??'')],
                $currentHeader
            );
            $headerVals = $this->collect($headerFields, $_POST['f'] ?? []);
            $one = $this->e->createSingleRow((int)$sessionId);
            if (!$one['created']) {
                flash('error', 'Daily Check tanggal ini sudah terisi. Sistem tidak menambahkan baris baru karena Daily Check X-Ray hanya satu kali per hari.');
                $this->redirect('entries/session/'.$sessionId);
            }
            $rid=(int)$one['id'];
            $this->e->saveSessionValues((int)$sessionId, $headerVals);
            $this->e->saveRowValues($rid, $vals);
            audit('create_daily', 'row', $rid, ['session_id'=>(int)$sessionId,'daily_once'=>true,'full_template'=>true]);
            flash('success', 'Daily Check X-Ray berhasil disimpan sebagai satu lembar lengkap.');
            $this->redirect('entries/session/'.$sessionId);
        }

        $rid = $this->e->createRow((int)$sessionId);
        $this->e->saveRowValues($rid, $vals);
        $row = $this->e->row($rid);
        audit('create', 'row', $rid, ['session_id'=>(int)$sessionId,'sequence_no'=>(int)($row['sequence_no'] ?? 0)]);
        flash('success', 'Baris No. '.(int)($row['sequence_no'] ?? 0).' berhasil ditambahkan.');
        $this->redirect('entries/session/'.$sessionId);
    }

    public function editRow(string $rowId): void {
        $this->requireOperator();
        $r = $this->e->row((int)$rowId);
        if (!$r) abort(404, 'Baris tidak ditemukan.');
        $this->authorizedSession((int)$r['session_id']);
        $this->requireRowOwnerOrAdmin($r);
        $s = $this->e->session((int)$r['session_id']);
        $data = [
            'title'        => 'Edit Baris',
            'session'      => $s,
            'row'          => $r,
            'fields'       => $this->l->fields((int)$s['logbook_id'], 'detail'),
            'headerFields' => $this->headerFields(
                ['id'=>(int)$s['logbook_id'],'code'=>(string)($s['code']??'')],
                $this->e->sessionValues((int)$r['session_id'])
            ),
            'headerValues' => $this->e->sessionValues((int)$r['session_id'])
        ];
        $code=$s['code']??''; $view = is_xray_single_code($code) ? 'entries/xray_single_row_edit' : (is_xray_multi_code($code) ? 'entries/xray_multi_row_edit' : 'entries/row_edit');
        $this->view($view, $data);
    }

    public function updateRow(string $rowId): void {
        $this->requireOperator();
        $this->csrf();
        $r = $this->e->row((int)$rowId);
        if (!$r) abort(404, 'Baris tidak ditemukan.');
        $this->authorizedSession((int)$r['session_id']);
        $this->requireRowOwnerOrAdmin($r);
        $s = $this->e->session((int)$r['session_id']);
        $isXray = is_xray_special_code((string)($s['code'] ?? ''));
        $vals = $this->collect($this->l->fields((int)$s['logbook_id'], 'detail'), $_POST['f'] ?? []);
        $this->e->saveRowValues((int)$rowId, $vals);
        if ($isXray) {
            $currentHeader = $this->e->sessionValues((int)$r['session_id']);
            $headerFields = $this->headerFields(
                ['id'=>(int)$s['logbook_id'],'code'=>(string)($s['code']??'')],
                $currentHeader
            );
            $headerVals = $this->collect($headerFields, $_POST['f'] ?? []);
            $this->e->saveSessionValues((int)$r['session_id'], $headerVals);
        }
        audit('update', 'row', (int)$rowId, ['full_template'=>$isXray]);
        flash('success', $isXray ? 'Lembar Daily Check diperbarui.' : 'Baris diperbarui.');
        $this->redirect('entries/session/'.$r['session_id']);
    }

    public function deleteRow(string $rowId): void {
        $this->requireAdmin();
        $this->csrf();
        $r = $this->e->row((int)$rowId);
        if (!$r) abort(404, 'Baris tidak ditemukan.');
        $this->e->deleteRow((int)$rowId);
        audit('delete', 'row', (int)$rowId);
        flash('success', 'Baris dihapus.');
        $this->redirect('entries/session/'.$r['session_id']);
    }

    public function editSession(string $sessionId): void {
        $this->requireAdmin();
        $s = $this->authorizedSession((int)$sessionId);
        $lb=$this->l->find((int)$s['logbook_id']);
        if(!$lb) abort(404,'Logbook tidak ditemukan.');
        $values=$this->e->sessionValues((int)$sessionId);
        $rows = is_xray_special_code((string)($lb['code'] ?? '')) ? $this->e->rows((int)$sessionId) : [];
        $firstRow = $rows[0] ?? null;
        $this->view('entries/session_form', [
            'title'        => is_xray_special_code((string)($lb['code'] ?? '')) ? 'Edit Lembar Daily Check X-Ray' : 'Edit Header Logbook',
            'logbook'      => $lb,
            'fields'       => $this->headerFields($lb,$values),
            'session'      => $s,
            'values'       => $values,
            'detailFields' => is_xray_special_code((string)($lb['code'] ?? '')) ? $this->l->fields((int)$s['logbook_id'], 'detail') : [],
            'detailValues' => $firstRow['values'] ?? [],
            'detailRowId'  => $firstRow['id'] ?? null
        ]);
    }

    public function updateSession(string $sessionId): void {
        $this->requireAdmin();
        $this->csrf();
        $s = $this->authorizedSession((int)$sessionId);
        $lb=$this->l->find((int)$s['logbook_id']);
        if(!$lb) abort(404,'Logbook tidak ditemukan.');

        $date = $_POST['session_date'] ?? $s['session_date'];
        $isXray = is_xray_special_code((string)($lb['code'] ?? ''));
        $shift = $isXray ? '' : trim($_POST['shift'] ?? '');

        $duplicate = $isXray
            ? $this->e->findExistingDailySession((int)$s['logbook_id'], $date, (int)$sessionId)
            : $this->e->findExistingSession((int)$s['logbook_id'], $date, $shift, (int)$sessionId);
        if ($duplicate) abort(422, $isXray
            ? 'Header tidak dapat disimpan karena Daily Check X-Ray pada tanggal tersebut sudah ada. Satu jenis Daily Check hanya satu kali per hari.'
            : 'Header tidak dapat disimpan karena sesi dengan tanggal dan dinas/regu/shift yang sama sudah ada.');

        $fields = $this->headerFields($lb,$this->e->sessionValues((int)$sessionId));
        $vals = $this->collect($fields, $_POST['f'] ?? []);
        $dailyKey = $isXray ? 'XRAY-DAY-'.(int)$s['logbook_id'].'-'.$date : null;
        $this->e->updateSessionMeta((int)$sessionId, $date, $shift, $dailyKey);
        $this->e->saveSessionValues((int)$sessionId, $vals);
        if ($isXray) {
            $detailFields = $this->l->fields((int)$s['logbook_id'], 'detail');
            $detailVals = $this->collect($detailFields, $_POST['f'] ?? []);
            $rows = $this->e->rows((int)$sessionId);
            if ($rows) {
                $this->e->saveRowValues((int)$rows[0]['id'], $detailVals);
            } else {
                $one = $this->e->createSingleRow((int)$sessionId);
                $this->e->saveRowValues((int)$one['id'], $detailVals);
            }
        }
        audit('update', 'session', (int)$sessionId, ['full_template'=>$isXray]);
        flash('success', $isXray ? 'Lembar Daily Check diperbarui.' : 'Header diperbarui.');
        $this->redirect('entries/session/'.$sessionId);
    }

    public function deleteSession(string $sessionId): void {
        $this->requireAdmin();
        $this->csrf();
        $this->e->deleteSession((int)$sessionId);
        audit('delete', 'session', (int)$sessionId);
        flash('success', 'Sesi logbook dihapus.');
        $this->redirect('entries');
    }


    public function hideSession(string $sessionId): void {
        $this->requirePetugas();
        $this->csrf();
        $s=$this->authorizedSession((int)$sessionId);
        $this->e->hideSessionForUser((int)$sessionId,(int)Auth::id());
        audit('hide','session',(int)$sessionId,['logbook_id'=>(int)$s['logbook_id']]);
        flash('success','Data logbook disembunyikan dari daftar Anda. Data tidak dihapus dan tetap dapat dilihat Admin/Supervisor.');
        $this->redirect('entries');
    }

    public function unhideSession(string $sessionId): void {
        $this->requirePetugas();
        $this->csrf();
        $this->authorizedSession((int)$sessionId);
        $this->e->unhideSessionForUser((int)$sessionId,(int)Auth::id());
        audit('unhide','session',(int)$sessionId);
        flash('success','Data logbook ditampilkan kembali.');
        $this->redirect('entries?show_hidden=1');
    }

    private function authorizedSession(int $id): array {
        $s = $this->e->session($id);
        if (!$s) abort(404, 'Sesi logbook tidak ditemukan.');
        $this->requireAssignedLogbook(['id'=>(int)$s['logbook_id'],'code'=>(string)($s['code']??'')]);
        return $s;
    }

    private function requireAssignedLogbook(array $logbook): void {
        if(!Auth::isPetugas()) return;
        $id=(int)($logbook['id']??$logbook['logbook_id']??0); $code=(string)($logbook['code']??'');
        $daily=is_xray_special_code($code);
        if(!(new AssignmentModel())->isAssigned((int)Auth::id(),$id,$daily)) {
            abort(403,$daily?'Daily Check ini ditugaskan kepada Petugas lain.':'Akun Anda tidak ditugaskan untuk logbook ini.');
        }
    }

    private function requireRowOwnerOrAdmin(array $row): void {
        if (!Auth::isAdmin() && (int)($row['created_by'] ?? 0) !== Auth::id()) {
            abort(403, 'Petugas hanya dapat mengedit baris yang dibuat oleh akunnya sendiri.');
        }
    }

    private function headerFields(array $logbook, array $currentValues=[]): array {
        $logbookId=(int)($logbook['id']??$logbook['logbook_id']??0);
        $fields=$this->l->fields($logbookId,'header');
        $code=(string)($logbook['code']??'');
        if(!is_xray_special_code($code)) return $fields;

        $master=$this->xm->optionsByFieldKey();
        foreach($fields as &$f){
            $key=(string)($f['field_key']??'');
            if(!array_key_exists($key,$master)) continue;
            $values=$master[$key];
            $current=trim((string)($currentValues[$f['id']]??''));
            if($current!=='' && !in_array($current,$values,true)) $values[]=$current;
            $f['field_type']='select';
            $f['options']=implode("\n",$values);
            $f['help_text']='Pilihan diambil dari Master Data X-Ray. Hubungi Admin bila pilihan belum tersedia.';
        }
        unset($f);
        return $fields;
    }

    private function collect(array $fields, array $posted): array {
        $o = [];
        foreach ($fields as $f) {
            $v = $posted[$f['id']] ?? '';
            if (($f['field_key'] ?? '') === 'ttd_personel_1') $v = xray_normalize_signature_data($v);
            if ((int)$f['required'] === 1 && trim((string)$v) === '') abort(422, 'Kolom '.$f['label'].' wajib diisi.');
            $o[(int)$f['id']] = $v;
        }
        return $o;
    }
}
