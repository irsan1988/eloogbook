<?php
class Router {
    private array $routes = [
        'GET /' => [DashboardController::class,'index'],
        'GET /login' => [AuthController::class,'showLogin'],
        'POST /login' => [AuthController::class,'login'],
        'POST /login/qr' => [AuthController::class,'loginQr'],
        'POST /login/barcode' => [AuthController::class,'loginBarcode'],
        'POST /logout' => [AuthController::class,'logout'],

        'GET /logbooks' => [LogbookController::class,'index'],
        'GET /daily-check' => [LogbookController::class,'dailyCheck'],
        'POST /daily-check/{id}/toggle' => [LogbookController::class,'toggleDailyCheck'],
        'GET /xray-master' => [XrayMasterController::class,'index'],
        'POST /xray-master/{type}' => [XrayMasterController::class,'store'],
        'POST /xray-master/{type}/{id}/update' => [XrayMasterController::class,'update'],
        'POST /xray-master/{type}/{id}/delete' => [XrayMasterController::class,'delete'],
        'GET /logbooks/create' => [LogbookController::class,'create'],
        'POST /logbooks' => [LogbookController::class,'store'],
        'GET /logbooks/{id}/edit' => [LogbookController::class,'edit'],
        'POST /logbooks/{id}/update' => [LogbookController::class,'update'],
        'POST /logbooks/{id}/delete' => [LogbookController::class,'delete'],
        'GET /logbooks/{id}/fields' => [LogbookController::class,'fields'],
        'POST /logbooks/{id}/fields' => [LogbookController::class,'storeField'],
        'POST /fields/{id}/update' => [LogbookController::class,'updateField'],
        'POST /fields/{id}/delete' => [LogbookController::class,'deleteField'],

        'GET /entries' => [EntryController::class,'index'],
        'GET /entries/create/{logbookId}' => [EntryController::class,'createSession'],
        'POST /entries/create/{logbookId}' => [EntryController::class,'storeSession'],
        'GET /entries/session/{sessionId}' => [EntryController::class,'session'],
        'POST /entries/session/{sessionId}/row' => [EntryController::class,'storeRow'],
        'GET /entries/row/{rowId}/edit' => [EntryController::class,'editRow'],
        'POST /entries/row/{rowId}/update' => [EntryController::class,'updateRow'],
        'POST /entries/row/{rowId}/delete' => [EntryController::class,'deleteRow'],
        'GET /entries/session/{sessionId}/edit' => [EntryController::class,'editSession'],
        'POST /entries/session/{sessionId}/update' => [EntryController::class,'updateSession'],
        'POST /entries/session/{sessionId}/delete' => [EntryController::class,'deleteSession'],
        'POST /entries/session/{sessionId}/hide' => [EntryController::class,'hideSession'],
        'POST /entries/session/{sessionId}/unhide' => [EntryController::class,'unhideSession'],
        'GET /print/session/{sessionId}' => [PrintController::class,'session'],
        'GET /reports/print-range' => [ReportController::class,'printRange'],
        'GET /print/range' => [PrintController::class,'range'],
        'GET /notifications' => [NotificationController::class,'index'],

        'GET /assignments' => [AssignmentController::class,'index'],
        'POST /assignments/general/{id}' => [AssignmentController::class,'saveGeneral'],
        'POST /assignments/daily/{id}' => [AssignmentController::class,'saveDaily'],

        'GET /users' => [UserController::class,'index'],
        'POST /users' => [UserController::class,'store'],
        'POST /users/{id}/update' => [UserController::class,'update'],
        'POST /users/{id}/delete' => [UserController::class,'delete'],
        'POST /users/{id}/qr' => [UserController::class,'issueQr'],
        'GET /users/{id}/qr' => [UserController::class,'qr'],
        'POST /users/{id}/barcode' => [UserController::class,'issueBarcode'],
        'GET /users/{id}/barcode' => [UserController::class,'barcode'],
        'GET /audit' => [AuditController::class,'index'],
    ];

    public function dispatch(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if ($base && $base !== '/' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
        $path = '/'.trim($path,'/'); if ($path === '//') $path='/';
        foreach ($this->routes as $key => $handler) {
            [$rm,$rp] = explode(' ', $key, 2); if ($rm !== $method) continue;
            $pattern = preg_replace('#\{([A-Za-z][A-Za-z0-9_]*)\}#','(?P<$1>[^/]+)',$rp);
            if (preg_match('#^'.$pattern.'$#',$path,$m)) {
                $params=[]; foreach($m as $k=>$v) if(!is_int($k)) $params[$k]=$v;
                [$class,$fn]=$handler; $c=new $class(); $c->$fn(...array_values($params)); return;
            }
        }
        abort(404,'Halaman tidak ditemukan.');
    }
}
