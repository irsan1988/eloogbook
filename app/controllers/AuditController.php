<?php
class AuditController extends Controller { public function index(): void { $this->requireAdmin();$db=Database::conn();$rows=$db->query('SELECT a.*,u.name user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 500')->fetchAll();$this->view('audit/index',['title'=>'Audit Trail','rows'=>$rows]); } }
