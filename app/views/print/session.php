<?php
$days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months=[1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$fmtDate=function(string $date) use($days,$months): string {
    $ts=strtotime($date); if(!$ts)return $date;
    return $days[(int)date('w',$ts)].', '.date('d',$ts).'-'.$months[(int)date('n',$ts)].'-'.date('Y',$ts);
};
$fieldValueByKey=function(array $fields,array $values,string $key): string {
    foreach($fields as $f) if($f['field_key']===$key) return (string)($values[(int)$f['id']]??'');
    return '';
};
$isXray = stripos((string)$session['code'],'XRAY')!==false;
foreach($detailFields as $f){ if(in_array($f['field_key'],['test_1a','test_1b','test_2a','test_2b','test_3','test_4','test_5'],true)){$isXray=true;break;} }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($session['logbook_name'])?></title>
<style>
@page{size:A4 <?=e($session['orientation'])?>;margin:9mm 10mm 10mm}
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:#fff;color:#000}
body{font-family:"Times New Roman",Times,serif;font-size:11px}
.toolbar{position:fixed;top:12px;right:12px;z-index:99;display:flex;gap:7px;font-family:Arial,sans-serif}
.toolbar button,.toolbar a{border:0;border-radius:5px;background:#111;color:#fff;padding:9px 14px;text-decoration:none;cursor:pointer;font-size:12px}
.page{width:100%;margin:0 auto;position:relative;background:#fff}
.page-break{page-break-after:always}
.center{text-align:center}.right{text-align:right}.bold{font-weight:700}.upper{text-transform:uppercase}
.print-title{font-weight:700;text-align:center;text-transform:uppercase;line-height:1.15;font-size:13.5px;margin:0 0 8px}
.small{font-size:9px}.tiny{font-size:8px}

/* Format buku Random Check HBSCP */
.hbsc-meta{width:100%;border-collapse:collapse;margin:2px 0 8px;font-size:11px}
.hbsc-meta td{padding:1px 2px;border:0}
.hbsc-meta td:first-child{width:82px;font-weight:700;text-transform:uppercase}
.hbsc-meta td:nth-child(2){width:9px;text-align:center}
.hbsc-table{width:100%;border-collapse:collapse;table-layout:fixed}
.hbsc-table th,.hbsc-table td{border:1px solid #222;padding:4px 5px;vertical-align:middle}
.hbsc-table th{font-family:Arial,sans-serif;font-weight:400;text-align:center;text-transform:uppercase;height:10mm;font-size:9.5px;background:#fff}
.hbsc-table td{height:8.3mm;font-size:10.5px}
.hbsc-table .no{width:34px;text-align:center}
.hbsc-table .cell-center{text-align:center}
.extra-meta{width:100%;border-collapse:collapse;margin:4px 0 7px}
.extra-meta td{border:0;padding:1px 2px}
.extra-meta td:first-child{width:150px;font-weight:700}
.hbsc-sign{margin-top:13px;display:flex;justify-content:flex-end}
.hbsc-sign>div{width:220px;text-align:center}.sign-space{height:36px}.sign-line{display:inline-block;min-width:155px;border-bottom:1px dotted #333}

/* Format checklist X-Ray */
.xray-page{font-size:10px}
.xray-title{font-size:12px;line-height:1.08;margin-bottom:5px}
.xray-head{width:100%;border-collapse:collapse;margin-bottom:5px}
.xray-head td{border:1px solid #777;padding:2px 5px;height:5.3mm}
.xray-head td:first-child{width:29%;font-size:9.5px}.xray-head td:nth-child(2){width:2%;text-align:center}
.legend{font-size:9px;line-height:1.7;margin:5px 0 3px 5px}
.legend .sym{display:inline-grid;place-items:center;width:14px;height:14px;border:1px solid #333;margin-right:5px;font-family:Arial,sans-serif;font-weight:700}
.generator-title{text-align:center;font-size:11px;font-weight:700;margin:0 0 3px}
.test-board{border:1px solid #777;height:104mm;padding:5mm 6mm;position:relative;overflow:hidden}
.test-label{font-family:Arial,sans-serif;font-size:9px;font-weight:700;letter-spacing:.4px}
.mark-circle{width:18px;height:18px;border:1px dashed #444;border-radius:50%;display:inline-grid;place-items:center;font-family:Arial,sans-serif;font-size:12px;font-weight:700;background:#fff}
.mark-circle.bad{font-size:11px}
.pattern-lines{background:repeating-linear-gradient(90deg,#777 0,#777 1px,transparent 1px,transparent 3px);border:1px solid #888}
.pattern-grid{background-image:linear-gradient(#999 1px,transparent 1px),linear-gradient(90deg,#999 1px,transparent 1px);background-size:3px 3px;border:1px solid #777}
.t2a{position:absolute;left:19%;top:8%;width:18%;height:25%}.t2a .pattern-grid{position:absolute;left:0;top:23%;width:53%;height:58%}.t2a .sugar{position:absolute;right:0;top:25%;width:45%;height:57%;border:1px solid #999;background:#eee;text-align:center;padding-top:18px;font-size:7px}.t2a .mark-circle{position:absolute;left:47%;top:1%}
.t3{position:absolute;left:41%;top:7%;width:49%;height:28%}.t3bar{display:grid;grid-template-columns:repeat(9,1fr);height:61%;margin-top:5px;border-top:7px solid #222}.t3bar span{border-right:1px solid #555;background:repeating-linear-gradient(90deg,#888 0,#888 1px,transparent 1px,transparent 2px)}.t3bar span:nth-child(n+6){background:#fff}.t3nums{display:grid;grid-template-columns:repeat(9,1fr);font-size:6.5px;text-align:center;margin-top:2px}.t3 .mark-circle{position:absolute;left:7%;top:3px}
.t1{position:absolute;left:14%;bottom:9%;width:30%;height:38%}.t1boxes{display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:repeat(3,1fr);gap:3px;height:72%;margin-top:7px}.t1boxes span{border:1px solid #aaa}.t1boxes span:nth-child(odd){background:#eee}.t1 .m1a{position:absolute;left:2%;bottom:4%}.t1 .m1b{position:absolute;left:38%;bottom:4%}.t1 .label1a{position:absolute;left:-4%;bottom:-10%;}.t1 .label1b{position:absolute;left:49%;bottom:-10%;}
.t2b{position:absolute;left:28%;top:37%;width:18%;height:13%}.t2b .mini{display:flex;gap:8px;margin-top:4px}.t2b .mini span{width:24px;height:24px;border:1px solid #999;background:#eee}.t2b .mark-circle{position:absolute;right:-3px;top:8px}
.t4{position:absolute;left:48%;bottom:10%;width:31%;height:37%;border:1px solid #aaa;padding:6px}.t4 .bar1,.t4 .bar2,.t4 .bar3{position:absolute;width:36%;height:14%;background:repeating-linear-gradient(0deg,#777 0,#777 1px,transparent 1px,transparent 3px)}.t4 .bar1{left:8%;top:10%}.t4 .bar2{left:8%;top:40%}.t4 .bar3{left:8%;bottom:10%}.t4 .mark-circle{position:absolute;left:35%;top:40%}.t4 .mark-circle.m2{left:auto;right:12%;top:46%}.t4 .caption{position:absolute;bottom:-13px;left:42%;}
.t5{position:absolute;right:5%;bottom:10%;width:12%;height:37%;border:1px solid #aaa;padding-top:12px;text-align:center}.t5 .mark-circle{display:grid;margin:0 auto 9px}.t5 .caption{position:absolute;bottom:-13px;left:35%}
.result-box{border:1px solid #777;border-top:0;padding:5px 8px;min-height:27mm}.result-line{display:flex;align-items:center;gap:7px;margin-bottom:5px;font-size:9.5px}.check-square{width:17px;height:11px;border:1px solid #333;display:inline-grid;place-items:center;font-family:Arial,sans-serif;font-weight:700}.note-line{display:flex;gap:6px;margin-top:6px}.note-line .note-text{flex:1;min-height:11mm;border-bottom:1px solid #777;white-space:pre-wrap}
.xray-sign{border:1px solid #777;border-top:0;min-height:36mm;padding:7px 8px;position:relative}.xray-sign .person{line-height:2}.xray-sign .signature{position:absolute;right:9%;top:8mm;width:26%;height:20mm;border-bottom:1px dotted #555;text-align:center;padding-top:14mm}

/* Fallback form */
.generic-form{border:1px solid #444;margin-top:8px}.generic-row{display:grid;grid-template-columns:34% 66%;border-bottom:1px solid #777;min-height:9mm}.generic-row:last-child{border-bottom:0}.generic-row>div{padding:5px}.generic-row>div:first-child{border-right:1px solid #777;font-weight:700}

@media print{
 .toolbar{display:none!important}
 body{print-color-adjust:exact;-webkit-print-color-adjust:exact}
 .page{break-inside:avoid}
}
</style>
</head>
<body>
<div class="toolbar"><a href="<?=e(url('entries/session/'.$session['id']))?>">KEMBALI</a><button onclick="window.print()">CETAK</button></div>

<?php if($session['print_layout']==='table'): ?>
<div class="page">
  <div class="print-title"><?=e($session['logbook_name'])?></div>
  <table class="hbsc-meta">
    <tr><td>HARI / TGL</td><td>:</td><td><?=e($fmtDate($session['session_date']))?></td></tr>
    <tr><td>DINAS / REGU</td><td>:</td><td><?=e($session['shift'] ?: '-')?></td></tr>
  </table>

  <?php
  $extra=[];
  foreach($headerFields as $f){
      if(in_array($f['field_key'],['hari_tanggal','tanggal','tgl','date','shift','dinas_regu'],true)) continue;
      $extra[]=$f;
  }
  ?>
  <?php if($extra):?><table class="extra-meta"><?php foreach($extra as $f):?><tr><td><?=e(strtoupper($f['label']))?></td><td>: <?=format_value($f,$headerValues[$f['id']]??'')?></td></tr><?php endforeach;?></table><?php endif;?>

  <table class="hbsc-table">
    <thead><tr><th class="no">NO</th><?php foreach($detailFields as $f):?><th><?=e($f['label'])?></th><?php endforeach;?></tr></thead>
    <tbody>
    <?php foreach($rows as $r):?>
      <tr><td class="no"><?=e($r['sequence_no'])?></td><?php foreach($detailFields as $f):?><td class="<?=$f['field_type']==='time'?'cell-center':''?>"><?=format_value($f,$r['values'][$f['id']]??'')?></td><?php endforeach;?></tr>
    <?php endforeach;?>
    <?php $blank=max(0,16-count($rows)); for($i=0;$i<$blank;$i++):?>
      <tr><td class="no">&nbsp;</td><?php foreach($detailFields as $f):?><td>&nbsp;</td><?php endforeach;?></tr>
    <?php endfor;?>
    </tbody>
  </table>
  <div class="hbsc-sign"><div>Personel Pengamanan Penerbangan,<div class="sign-space"></div><span class="sign-line"><?=e($session['creator']?:'')?></span></div></div>
</div>

<?php elseif($isXray): ?>
<?php $printRows=$rows?:[['sequence_no'=>1,'values'=>[]]]; $total=count($printRows); foreach($printRows as $idx=>$r):
  $v=[]; foreach($detailFields as $f)$v[$f['field_key']]=(string)($r['values'][$f['id']]??'');
  $hv=[]; foreach($headerFields as $f)$hv[$f['field_key']]=(string)($headerValues[$f['id']]??'');
  $mark=function(string $key) use($v):string{ $x=strtolower(trim($v[$key]??'')); return in_array($x,['terpenuhi','pass','ya','1','✓'],true)?'✓':(in_array($x,['tidak terpenuhi','fail','tidak','0','x'],true)?'×':''); };
?>
<div class="page xray-page <?=$idx<$total-1?'page-break':''?>">
  <div class="print-title xray-title"><?=e($session['logbook_name'])?><br><span class="tiny">LEMBAR <?=($idx+1)?> DARI <?=$total?></span></div>
  <table class="xray-head">
    <?php foreach($headerFields as $f):?>
      <tr><td><?=e($f['label'])?></td><td>:</td><td><?=format_value($f,$headerValues[$f['id']]??'')?></td></tr>
    <?php endforeach;?>
    <?php if(!$headerFields):?>
      <tr><td>Tanggal &amp; Waktu Pengujian</td><td>:</td><td><?=e($fmtDate($session['session_date']))?></td></tr>
    <?php endif;?>
  </table>

  <div class="legend"><span class="sym">✓</span>: Terpenuhi<br><span class="sym">×</span>: Tidak terpenuhi</div>
  <div class="generator-title">GENERATOR ATAS/BAWAH</div>
  <div class="test-board">
    <div class="t2a"><span class="test-label">TEST 2a</span><div class="pattern-grid"></div><div class="sugar">SALT&nbsp;&nbsp;&nbsp;&nbsp; SUGAR</div><span class="mark-circle <?=$mark('test_2a')==='×'?'bad':''?>"><?=$mark('test_2a')?></span></div>
    <div class="t3"><span class="test-label" style="position:absolute;left:39%;top:-10px">TEST 3</span><span class="mark-circle <?=$mark('test_3')==='×'?'bad':''?>"><?=$mark('test_3')?></span><div class="t3bar"><?php for($i=0;$i<9;$i++):?><span></span><?php endfor;?></div><div class="t3nums"><?php foreach([14,16,18,20,22,24,26,28,30] as $n):?><span><?=$n?></span><?php endforeach;?></div></div>
    <div class="t2b"><span class="test-label">TEST 2b</span><div class="mini"><span></span><span></span><span></span></div><span class="mark-circle <?=$mark('test_2b')==='×'?'bad':''?>"><?=$mark('test_2b')?></span></div>
    <div class="t1"><div class="t1boxes"><?php for($i=0;$i<12;$i++):?><span></span><?php endfor;?></div><span class="mark-circle m1a <?=$mark('test_1a')==='×'?'bad':''?>"><?=$mark('test_1a')?></span><span class="mark-circle m1b <?=$mark('test_1b')==='×'?'bad':''?>"><?=$mark('test_1b')?></span><span class="test-label label1a">TEST 1a</span><span class="test-label label1b">TEST 1b</span></div>
    <div class="t4"><span class="tiny">1.5 mm gaps</span><div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><span class="mark-circle <?=$mark('test_4')==='×'?'bad':''?>"><?=$mark('test_4')?></span><span class="mark-circle m2 <?=$mark('test_4')==='×'?'bad':''?>"><?=$mark('test_4')?></span><span class="test-label caption">TEST 4</span></div>
    <div class="t5"><span class="mark-circle <?=$mark('test_5')==='×'?'bad':''?>"><?=$mark('test_5')?></span><span class="mark-circle <?=$mark('test_5')==='×'?'bad':''?>"><?=$mark('test_5')?></span><span class="test-label caption">TEST 5</span></div>
  </div>
  <div class="result-box">
    <div class="result-line"><b>HASIL</b>&nbsp;&nbsp;: <span class="check-square"><?=strtolower($v['hasil']??'')==='pass'?'✓':''?></span> PASS</div>
    <div class="result-line" style="margin-left:44px"><span class="check-square"><?=strtolower($v['hasil']??'')==='fail'?'✓':''?></span> FAIL <span class="tiny">*) : Tidak dioperasikan dan hubungi personel faskampen.</span></div>
    <div class="note-line"><b>CATATAN*)</b><span>:</span><div class="note-text"><?=e($v['catatan']??'')?></div></div>
    <div class="tiny" style="margin-left:61px;margin-top:2px">Apabila salah satu test tidak terpenuhi.</div>
  </div>
  <div class="xray-sign">
    <div>Personel Pengamanan Penerbangan :</div>
    <div class="person">1. <span style="display:inline-block;min-width:190px;border-bottom:1px dotted #666"><?=e($session['creator']?:'')?></span><br>2. <span style="display:inline-block;min-width:190px;border-bottom:1px dotted #666">&nbsp;</span></div>
    <div class="signature">Paraf / Tanda Tangan</div>
  </div>
</div>
<?php endforeach;?>

<?php else: ?>
<div class="page">
  <div class="print-title"><?=e($session['logbook_name'])?></div>
  <table class="xray-head">
    <tr><td>Hari / Tanggal</td><td>:</td><td><?=e($fmtDate($session['session_date']))?></td></tr>
    <?php if($session['shift']):?><tr><td>Dinas / Regu / Shift</td><td>:</td><td><?=e($session['shift'])?></td></tr><?php endif;?>
    <?php foreach($headerFields as $f):?><tr><td><?=e($f['label'])?></td><td>:</td><td><?=format_value($f,$headerValues[$f['id']]??'')?></td></tr><?php endforeach;?>
  </table>
  <?php foreach(($rows?:[['sequence_no'=>1,'values'=>[]]]) as $r):?>
    <div class="generic-form">
      <?php foreach($detailFields as $f):?><div class="generic-row"><div><?=e($f['label'])?></div><div><?=format_value($f,$r['values'][$f['id']]??'')?></div></div><?php endforeach;?>
    </div>
  <?php endforeach;?>
  <div class="hbsc-sign"><div>Personel Pengamanan Penerbangan,<div class="sign-space"></div><span class="sign-line"><?=e($session['creator']?:'')?></span></div></div>
</div>
<?php endif;?>
</body>
</html>
