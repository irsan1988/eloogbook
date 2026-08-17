<?php
require_once __DIR__.'/QrCode.php';
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path=''): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($base === '/' || $base === '.') $base='';
    return $base . '/' . ltrim($path,'/');
}
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_token" value="'.e(csrf_token()).'">'; }
function flash(string $key, ?string $value=null): ?string { if ($value!==null){$_SESSION['_flash'][$key]=$value;return null;} $v=$_SESSION['_flash'][$key]??null; unset($_SESSION['_flash'][$key]); return $v; }
function old(string $key, mixed $default=''): mixed { return $_SESSION['_old'][$key] ?? $default; }
function abort(int $code, string $message): never { http_response_code($code); echo '<!doctype html><meta charset="utf-8"><title>'.$code.'</title><style>body{font-family:Arial;margin:40px}.box{max-width:700px;padding:24px;border:1px solid #ddd;border-radius:12px}</style><div class="box"><h2>'.$code.'</h2><p>'.e($message).'</p><p><a href="'.e(url('')).'">Kembali</a></p></div>'; exit; }
function audit(string $action, string $entity, ?int $entityId=null, array $meta=[]): void {
    try { $db=Database::conn(); $s=$db->prepare('INSERT INTO audit_logs(user_id,action,entity,entity_id,meta,ip_address,created_at) VALUES(?,?,?,?,?,?,NOW())'); $s->execute([Auth::id(),$action,$entity,$entityId,json_encode($meta,JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'']); } catch(Throwable $e) {}
}
function field_input(array $field, mixed $value='', string $prefix='f'): string {
    $name=$prefix.'['.(int)$field['id'].']'; $id=$prefix.'_'.(int)$field['id']; $req=((int)$field['required']===1?' required':''); $val=e($value);
    $label='<label class="form-label" for="'.e($id).'">'.e($field['label']).(((int)$field['required']===1)?' <span class="text-danger">*</span>':'').'</label>';
    $help=!empty($field['help_text'])?'<div class="form-text">'.e($field['help_text']).'</div>':'';
    $type=$field['field_type'];
    if($type==='textarea') $input='<textarea class="form-control" id="'.e($id).'" name="'.e($name).'" rows="3"'.$req.'>'.$val.'</textarea>';
    elseif($type==='select') { $opts=array_filter(array_map('trim',explode("\n",$field['options']??''))); $input='<select class="form-select" id="'.e($id).'" name="'.e($name).'"'.$req.'><option value="">-- pilih --</option>'; foreach($opts as $o){$sel=((string)$value===$o?' selected':'');$input.='<option'.$sel.'>'.e($o).'</option>';}$input.='</select>'; }
    elseif($type==='checkbox') { $checked=in_array((string)$value,['1','Ya','yes','on'],true)?' checked':''; $input='<div class="form-check"><input type="hidden" name="'.e($name).'" value="0"><input class="form-check-input" type="checkbox" id="'.e($id).'" name="'.e($name).'" value="1"'.$checked.'><label class="form-check-label" for="'.e($id).'">Ya / terpenuhi</label></div>'; }
    else { $htmlType=in_array($type,['text','number','date','time','datetime-local'],true)?$type:'text'; $input='<input class="form-control" type="'.e($htmlType).'" id="'.e($id).'" name="'.e($name).'" value="'.$val.'"'.$req.'>'; }
    return '<div class="mb-3">'.$label.$input.$help.'</div>';
}
function format_value(array $field, mixed $value): string { if(($field['field_key']??'')==='ttd_personel_1') return xray_signature_src($value)!==''?'Tanda tangan tersimpan':'-'; if($field['field_type']==='checkbox') return ((string)$value==='1')?'✓':'-'; return e($value); }

function xray_normalize_signature_data(mixed $value): string {
    $value=trim((string)$value);
    if($value==='') return '';
    if(strlen($value)>350000) return '';
    if(!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=\r\n]+)$#',$value,$m)) return '';
    $raw=base64_decode(preg_replace('/\s+/','',$m[1]),true);
    if($raw===false || strlen($raw)>250000 || substr($raw,0,8)!=="\x89PNG\r\n\x1a\n") return '';
    return 'data:image/png;base64,'.base64_encode($raw);
}
function xray_signature_src(mixed $value): string { return xray_normalize_signature_data($value); }

function xray_single_checkbox_map(): array {
    return [
        'box_t2a'=>[32.1995,31.5677,2.6455,1.7121],
        'box_t3_14'=>[45.8050,31.7282,2.6455,1.7121],
        'box_t3_16'=>[49.9622,31.7282,2.6455,1.7121],
        'box_t3_18'=>[54.1194,31.7282,2.6455,1.7121],
        'box_t3_20'=>[58.2766,31.7282,2.5699,1.7121],
        'box_t3_22'=>[62.4339,31.7282,2.6455,1.7121],
        'box_t3_24'=>[66.7423,31.7282,2.5699,1.7121],
        'box_t3_26'=>[70.8239,31.7282,2.6455,1.7121],
        'box_t3_28'=>[75.1323,31.7282,2.6455,1.7121],
        'box_t3_30'=>[79.1383,31.7282,2.6455,1.7121],
        'box_t2b'=>[43.0083,37.4532,2.5699,1.7121],
        'box_t1_36_1'=>[21.8443,41.3055,2.6455,1.7121],
        'box_t1_36_2'=>[28.0423,41.3055,2.5699,1.7121],
        'box_t1_36_3'=>[33.3333,41.3055,2.6455,1.7121],
        'box_t1_36_4'=>[39.3802,41.3055,2.5699,1.7121],
        'box_t1_32_1'=>[21.8443,44.0342,2.6455,1.7121],
        'box_t1_32_2'=>[28.0423,44.0342,2.5699,1.7121],
        'box_t1_32_3'=>[33.3333,44.0342,2.6455,1.7121],
        'box_t1_32_4'=>[39.3802,44.0342,2.5699,1.7121],
        'box_t1_30_1'=>[21.8443,46.6025,2.6455,1.7121],
        'box_t1_30_2'=>[28.0423,46.6025,2.5699,1.7121],
        'box_t1_30_3'=>[33.3333,46.6025,2.6455,1.7121],
        'box_t1_30_4'=>[39.3802,46.6025,2.5699,1.7121],
        'box_t1_24_1'=>[21.8443,49.0102,2.6455,1.7121],
        'box_t1_24_2'=>[28.0423,49.0102,2.5699,1.7121],
        'box_t1_24_3'=>[33.3333,49.0102,2.6455,1.7121],
        'box_t1_24_4'=>[39.3802,49.0102,2.5699,1.7121],
        'box_t4_15_h'=>[52.2298,40.7170,2.6455,1.7121],
        'box_t4_15_v'=>[62.2071,41.5730,2.5699,1.7121],
        'box_t4_20_h'=>[56.9161,44.8903,2.6455,1.7121],
        'box_t4_20_v'=>[67.1202,45.2113,2.6455,1.7121],
        'box_t4_10_h'=>[52.2298,49.8662,2.6455,1.6586],
        'box_t4_10_v'=>[62.3583,48.2076,2.6455,1.7121],
        'box_t5_065'=>[78.2313,40.9310,2.5699,1.7121],
        'box_t5_010'=>[78.3825,44.4088,2.5699,1.6586],
        'box_t5_015'=>[78.3825,47.7796,2.6455,1.6586],
        'result_pass'=>[26.4550,57.5174,2.6455,1.0701],
        'result_fail'=>[26.3039,62.3328,2.7211,1.0166],
    ];
}

function xray_multi_checkbox_map(): array {
    return [
        'u_t2a'=>[32.2177,28.0171,2.5806,1.6833],
        'u_t3_14'=>[45.8468,28.1312,2.5806,1.6833],
        'u_t3_16'=>[50.0806,28.1598,2.5806,1.6833],
        'u_t3_18'=>[54.1532,28.1598,2.5403,1.6833],
        'u_t3_20'=>[58.3065,28.1598,2.5403,1.6833],
        'u_t3_22'=>[62.4597,28.1312,2.5806,1.6833],
        'u_t3_24'=>[66.7742,28.1027,2.5806,1.6833],
        'u_t3_26'=>[70.8065,28.1598,2.6210,1.6833],
        'u_t3_28'=>[75.1613,28.1598,2.6210,1.6833],
        'u_t3_30'=>[79.1935,28.1027,2.5806,1.6833],
        'u_t2b'=>[43.0242,33.8659,2.5403,1.6833],
        'u_t1_36_1'=>[21.8548,37.7461,2.5806,1.6548],
        'u_t1_36_2'=>[28.1452,37.7461,2.5403,1.6548],
        'u_t1_36_3'=>[33.3871,37.7461,2.5403,1.6548],
        'u_t1_36_4'=>[39.4355,37.7461,2.5806,1.6548],
        'u_t1_32_1'=>[21.8548,40.5136,2.5806,1.6833],
        'u_t1_32_2'=>[28.1452,40.5136,2.5403,1.6833],
        'u_t1_32_3'=>[33.3871,40.5136,2.5403,1.6833],
        'u_t1_32_4'=>[39.4355,40.5136,2.5806,1.6833],
        'u_t1_30_1'=>[21.8548,43.0528,2.5806,1.6833],
        'u_t1_30_2'=>[28.1452,43.0528,2.5403,1.6833],
        'u_t1_30_3'=>[33.3871,43.0528,2.5403,1.6833],
        'u_t1_30_4'=>[39.4355,43.0528,2.5806,1.6833],
        'u_t1_24_1'=>[21.8548,45.4779,2.5806,1.6833],
        'u_t1_24_2'=>[28.1452,45.4779,2.5403,1.6833],
        'u_t1_24_3'=>[33.3871,45.4779,2.5403,1.6833],
        'u_t1_24_4'=>[39.4355,45.4779,2.5806,1.6833],
        'u_t4_15_h'=>[52.2177,37.1184,2.6210,1.6833],
        'u_t4_15_v'=>[62.1774,38.0029,2.5806,1.6548],
        'u_t4_20_h'=>[56.9355,41.3124,2.5806,1.6548],
        'u_t4_20_v'=>[67.1371,41.6262,2.5806,1.6833],
        'u_t4_10_h'=>[52.2984,46.2767,2.5806,1.6548],
        'u_t4_10_v'=>[62.3790,44.6505,2.5806,1.6548],
        'u_t5_065'=>[78.2661,37.3466,2.5403,1.6833],
        'u_t5_010'=>[78.3871,40.7989,2.5403,1.6548],
        'u_t5_015'=>[78.4274,44.1655,2.5806,1.6548],
        'l_t2a'=>[32.3387,53.6091,2.5000,1.6548],
        'l_t3_14'=>[46.0081,53.7518,2.5403,1.6548],
        'l_t3_16'=>[50.2419,53.7803,2.5806,1.6262],
        'l_t3_18'=>[54.3952,53.7803,2.5000,1.6262],
        'l_t3_20'=>[58.5484,53.7803,2.5403,1.6262],
        'l_t3_22'=>[62.7419,53.7518,2.5403,1.6548],
        'l_t3_24'=>[67.0565,53.7233,2.5403,1.6548],
        'l_t3_26'=>[71.1694,53.7803,2.5000,1.6262],
        'l_t3_28'=>[75.5242,53.7803,2.5403,1.6262],
        'l_t3_30'=>[79.5565,53.7233,2.5403,1.6548],
        'l_t2b'=>[43.1855,59.4864,2.5000,1.6262],
        'l_t1_36_1'=>[21.8952,63.3381,2.5403,1.6262],
        'l_t1_36_2'=>[28.1855,63.3381,2.5000,1.6262],
        'l_t1_36_3'=>[33.4677,63.3381,2.5000,1.6262],
        'l_t1_36_4'=>[39.5565,63.3381,2.5403,1.6262],
        'l_t1_32_1'=>[21.8952,66.1341,2.5403,1.6262],
        'l_t1_32_2'=>[28.1855,66.1341,2.5000,1.6262],
        'l_t1_32_3'=>[33.4677,66.1341,2.5000,1.6262],
        'l_t1_32_4'=>[39.5565,66.1341,2.5403,1.6262],
        'l_t1_30_1'=>[21.8952,68.6448,2.5403,1.6262],
        'l_t1_30_2'=>[28.1855,68.6448,2.5000,1.6262],
        'l_t1_30_3'=>[33.4677,68.6448,2.5000,1.6262],
        'l_t1_30_4'=>[39.5565,68.6448,2.5403,1.6262],
        'l_t1_24_1'=>[21.8952,71.0984,2.5403,1.6262],
        'l_t1_24_2'=>[28.1855,71.0984,2.5000,1.6262],
        'l_t1_24_3'=>[33.4677,71.0984,2.5000,1.6262],
        'l_t1_24_4'=>[39.5565,71.0984,2.5403,1.6262],
        'l_t4_15_h'=>[52.4597,62.7389,2.5403,1.6262],
        'l_t4_15_v'=>[62.4597,63.5949,2.5403,1.6548],
        'l_t4_20_h'=>[57.1774,66.9330,2.5403,1.5977],
        'l_t4_20_v'=>[67.4194,67.2468,2.5806,1.6262],
        'l_t4_10_h'=>[52.5000,71.8973,2.5403,1.5977],
        'l_t4_10_v'=>[62.6210,70.2425,2.5403,1.6262],
        'l_t5_065'=>[78.6290,62.9672,2.5000,1.6262],
        'l_t5_010'=>[78.7500,66.4194,2.5000,1.5977],
        'l_t5_015'=>[78.8306,69.7860,2.5403,1.6262],
        'result_pass'=>[26.5323,76.0913,2.5000,0.8845],
        'result_fail'=>[26.4516,78.4023,2.4597,0.8845],
    ];
}


function xray_single_codes(): array {
    return ['XRAY-SINGLE-BAGASI','XRAY-SINGLE-CABIN','XRAY-SINGLE-SSCP','XRAY-SINGLE-CARGO','XRAY-SINGLE-EXACT','XRAY-SINGLE-ACT'];
}

function is_xray_single_code(string $code): bool {
    return in_array($code, xray_single_codes(), true);
}

function xray_single_variant(string $code): string {
    return match($code) {
        'XRAY-SINGLE-CABIN' => 'CABIN',
        'XRAY-SINGLE-SSCP'  => 'SSCP',
        'XRAY-SINGLE-CARGO' => 'CARGO',
        default             => 'BAGASI',
    };
}

function xray_single_header_lines(string $code): array {
    $variant=xray_single_variant($code);
    return ['CHECK LIST PENGUJIAN HARIAN', 'MESIN X-RAY '.$variant.' JENIS SINGLE VIEW'];
}

function xray_multi_codes(): array {
    return ['XRAY-MULTI-BAGASI','XRAY-MULTI-CABIN','XRAY-MULTI-SSCP','XRAY-MULTI-CARGO','XRAY-MULTI-EXACT'];
}

function is_xray_multi_code(string $code): bool {
    return in_array($code, xray_multi_codes(), true);
}

function is_xray_special_code(string $code): bool {
    return is_xray_single_code($code) || is_xray_multi_code($code);
}

function xray_multi_variant(string $code): string {
    return match($code) {
        'XRAY-MULTI-CABIN' => 'CABIN',
        'XRAY-MULTI-SSCP'  => 'SSCP',
        'XRAY-MULTI-CARGO' => 'CARGO',
        default            => 'BAGASI',
    };
}

function xray_multi_header_lines(string $code): array {
    $variant=xray_multi_variant($code);
    $line1 = 'CHECK LIST PENGUJIAN HARIAN';
    return [$line1, 'MESIN X-RAY '.$variant.' JENIS MULTI VIEW'];
}

function xray_field_by_key(array $fields): array {
    $out=[];
    foreach($fields as $field) $out[$field['field_key']]=$field;
    return $out;
}

function xray_date_id(string $date): string {
    $months=[1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $ts=strtotime($date);
    if(!$ts) return $date;
    return date('j',$ts).' '.$months[(int)date('n',$ts)].' '.date('Y',$ts);
}

function qr_new_token(): string {
    return strtoupper(bin2hex(random_bytes(16)));
}

function qr_normalize_login_value(string $value): string {
    $value=trim($value);
    if(stripos($value,'AVSEC-LOGIN:')===0) $value=substr($value,12);
    return strtoupper(preg_replace('/[^A-F0-9]/','',$value)??'');
}

function qr_code_svg(string $text,int $scale=7,int $border=4): string {
    return AvsecQrCode::svg($text,$scale,$border);
}

// Alias internal untuk kompatibilitas route lama. Tampilan aplikasi menggunakan QR Code.
function barcode_new_token(): string { return qr_new_token(); }
function barcode_normalize_login_value(string $value): string { return qr_normalize_login_value($value); }
