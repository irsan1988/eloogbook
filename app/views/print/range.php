<?php
$days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$months=[1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$fmtDate=function(string $date) use($days,$months): string { $ts=strtotime($date); return $ts?$days[(int)date('w',$ts)].', '.date('d',$ts).'-'.$months[(int)date('n',$ts)].'-'.date('Y',$ts):$date; };
$code=$logbook['code']??'';
$isSingle=is_xray_single_code($code);
$isMulti=is_xray_multi_code($code);
$isExact=$isSingle||$isMulti;
$headerByKey=xray_field_by_key($headerFields); $detailByKey=xray_field_by_key($detailFields);
$map=$isMulti?xray_multi_checkbox_map():xray_single_checkbox_map();
$templateFile=$isMulti?'xray-multi-view-template.png':'xray-single-view-template.png';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?></title>
<style>
@page{size:A4 <?=e($logbook['orientation'])?>;margin:<?=$isExact?'0':'9mm 10mm 10mm'?>}*{box-sizing:border-box}html,body{margin:0;padding:0;background:#e5e7eb;color:#000}body{font-family:"Times New Roman",serif;font-size:11px}.toolbar{position:sticky;top:0;z-index:80;background:#111827;color:#fff;padding:10px;display:flex;gap:8px;justify-content:center;font-family:Arial,sans-serif}.toolbar a,.toolbar button{border:0;border-radius:6px;padding:8px 14px;text-decoration:none;background:#fff;color:#111827;font-weight:700;cursor:pointer}.summary{background:#fff;padding:8px 12px;text-align:center;font-family:Arial,sans-serif;font-size:12px}.page{position:relative;background:#fff;margin:10mm auto;page-break-after:always;overflow:hidden}.page:last-child{page-break-after:auto}.std-page{width:100%;min-height:190mm}.print-title{text-align:center;font-size:13px;font-weight:700;text-transform:uppercase;margin-bottom:8px}.meta{width:100%;border-collapse:collapse;margin-bottom:8px}.meta td{padding:2px;border:0}.meta td:first-child{width:100px;font-weight:700}.meta td:nth-child(2){width:10px}.data{width:100%;border-collapse:collapse;table-layout:fixed}.data th,.data td{border:1px solid #222;padding:4px;vertical-align:middle}.data th{text-align:center;font:9px Arial,sans-serif;text-transform:uppercase;height:9mm}.data td{height:8mm}.data .no{width:34px;text-align:center}.sign{margin-top:14px;display:flex;justify-content:flex-end}.sign>div{width:220px;text-align:center}.signspace{height:35px}.signline{display:inline-block;min-width:155px;border-bottom:1px dotted #333}.generic{border:1px solid #444;margin-top:8px}.generic-row{display:grid;grid-template-columns:34% 66%;border-bottom:1px solid #777;min-height:9mm}.generic-row:last-child{border-bottom:0}.generic-row>div{padding:5px}.generic-row>div:first-child{border-right:1px solid #777;font-weight:700}
/* X-Ray exact */.xray-page{width:210mm;height:297mm}.template{position:absolute;inset:0;width:100%;height:100%;display:block}.template-title{position:absolute;z-index:4;left:12.86%;top:9.11%;width:74.18%;height:2.48%;background:#d9d9d9;display:flex;flex-direction:column;align-items:center;justify-content:center;border-top:.25mm solid #333;border-bottom:.25mm solid #333;font-weight:700;font-size:4mm;line-height:1.02;text-align:center}.template-title .line1{font-style:italic}.template-title .mv,.template-title .sv{font-style:italic}.tick{position:absolute;z-index:5;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;font-weight:900;font-size:4.2mm;line-height:1}.tick.result{font-size:3.7mm}.overlay{position:absolute;z-index:4;display:flex;align-items:center;font-size:3.25mm;line-height:1;white-space:nowrap;overflow:hidden}.overlay.operator{left:37.415%;top:13.2691%;width:51.1716%;height:1.4981%}.overlay.date{left:37.415%;top:14.8208%;width:51.1716%;height:1.5516%}.overlay.lokasi{left:37.415%;top:16.4259%;width:51.1716%;height:1.4981%}.overlay.mesin{left:37.415%;top:17.9775%;width:51.1716%;height:1.4981%}.overlay.sertifikat{left:37.415%;top:19.5292%;width:51.1716%;height:1.5516%}.note-value{position:absolute;z-index:4;left:20.5%;top:69.55%;width:66%;min-height:2.45%;font-size:3mm;line-height:1.2;white-space:normal}.xray-page.multi .note-value{top:81.25%;min-height:1.75%;line-height:1.15}.person1,.person2{position:absolute;z-index:4;left:16.7%;width:27%;height:2.1%;font-size:3.1mm;display:flex;align-items:flex-end;background:#fff}.person1{top:78.65%}.person2{top:84.62%}.xray-page.multi .person1{left:16.7%;width:27%;height:1.75%;top:85.0%}.xray-page.multi .person2{left:16.7%;width:27%;height:1.75%;top:88.62%}.signature1{position:absolute;z-index:6;left:68.5%;top:76.9%;width:17.5%;height:4.4%;object-fit:contain;background:transparent}.xray-page.multi .signature1{left:68.5%;top:84.15%;width:17.5%;height:3.35%}.empty{padding:40px;text-align:center;background:#fff;margin:20px;font-family:Arial,sans-serif}
@media print{html,body{background:#fff}.toolbar,.summary{display:none!important}.page{margin:0;box-shadow:none}}
</style></head><body>
<div class="toolbar"><a href="<?=e(url('reports/print-range'))?>">Kembali</a><button onclick="window.print()">CETAK SEMUA</button></div>
<div class="summary"><?=e($logbook['name'])?> | <?=e(date('d-m-Y',strtotime($dateFrom)))?> s.d. <?=e(date('d-m-Y',strtotime($dateTo)))?> | <?=count($bundle)?> sesi</div>
<?php if(!$bundle):?><div class="empty">Tidak ada data pada rentang tanggal yang dipilih.</div><?php endif;?>
<?php foreach($bundle as $pack): $s=$pack['session']; $headerValues=$pack['headerValues']; $rows=$pack['rows']; ?>
  <?php if($isExact):
    $hv=function(string $key) use($headerByKey,$headerValues):string { return isset($headerByKey[$key])?(string)($headerValues[$headerByKey[$key]['id']]??''):''; };
    $printRows=$rows ?: [['values'=>[]]];
    foreach($printRows as $r): $rv=function(string $key) use($detailByKey,$r):string { return isset($detailByKey[$key])?(string)($r['values'][$detailByKey[$key]['id']]??''):''; };
  ?>
    <div class="page xray-page <?=$isMulti?'multi':'single'?>">
      <img class="template" src="<?=e(url('public/assets/templates/'.$templateFile))?>" alt="Checklist X-Ray">
      <?php if($isMulti || $isSingle):
        [$rangeTitleLine1,$rangeTitleLine2]=$isMulti?xray_multi_header_lines($code):xray_single_header_lines($code);
        $viewWord=$isMulti?'MULTI VIEW':'SINGLE VIEW';
        $viewClass=$isMulti?'mv':'sv';
      ?>
        <div class="template-title">
          <div class="line1"><?=e($rangeTitleLine1)?></div>
          <div><?=e(preg_replace('/\s+'.preg_quote($viewWord,'/').'$/','',$rangeTitleLine2))?> <span class="<?=e($viewClass)?>"><?=e($viewWord)?></span></div>
        </div>
      <?php endif;?>
      <div class="overlay operator"><?=e($hv('operator'))?></div>
      <div class="overlay date"><?=e(xray_date_id($s['session_date']).($hv('waktu_pengujian')!==''?' / '.$hv('waktu_pengujian').' WIB':''))?></div>
      <div class="overlay lokasi"><?=e($hv('lokasi'))?></div><div class="overlay mesin"><?=e($hv('mesin'))?></div><div class="overlay sertifikat"><?=e($hv('sertifikat'))?></div>
      <?php foreach($map as $key=>$pos): if($rv($key)!=='1') continue; ?><div class="tick <?=$key==='result_pass'||$key==='result_fail'?'result':''?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%;width:<?=$pos[2]?>%;height:<?=$pos[3]?>%;">✓</div><?php endforeach;?>
      <?php if(trim($rv('catatan'))!==''):?><div class="note-value"><?=nl2br(e($rv('catatan')))?></div><?php endif;?>
      <?php if(trim($rv('personel_1'))!==''):?><div class="person1"><?=e($rv('personel_1'))?></div><?php endif;?>
      <?php $sig1=xray_signature_src($rv('ttd_personel_1')); if($sig1!==''):?><img class="signature1" src="<?=e($sig1)?>" alt="Tanda tangan Personel 1"><?php endif;?>
      <?php if(trim($rv('personel_2'))!==''):?><div class="person2"><?=e($rv('personel_2'))?></div><?php endif;?>
    </div>
  <?php endforeach; ?>
  <?php elseif($logbook['print_layout']==='table'): ?>
    <div class="page std-page">
      <div class="print-title"><?=e($logbook['name'])?></div>
      <table class="meta"><tr><td>HARI / TGL</td><td>:</td><td><?=e($fmtDate($s['session_date']))?></td></tr><tr><td>DINAS / REGU</td><td>:</td><td><?=e($s['shift']?:'-')?></td></tr>
      <?php foreach($headerFields as $f): if(in_array($f['field_key'],['hari_tanggal','tanggal','tgl','date','shift','dinas_regu'],true)) continue; ?><tr><td><?=e(strtoupper($f['label']))?></td><td>:</td><td><?=format_value($f,$headerValues[$f['id']]??'')?></td></tr><?php endforeach;?></table>
      <table class="data"><thead><tr><th class="no">NO</th><?php foreach($detailFields as $f):?><th><?=e($f['label'])?></th><?php endforeach;?></tr></thead><tbody>
      <?php foreach($rows as $r):?><tr><td class="no"><?=e($r['sequence_no'])?></td><?php foreach($detailFields as $f):?><td><?=format_value($f,$r['values'][$f['id']]??'')?></td><?php endforeach;?></tr><?php endforeach;?>
      <?php for($i=0,$blank=max(0,16-count($rows));$i<$blank;$i++):?><tr><td>&nbsp;</td><?php foreach($detailFields as $f):?><td>&nbsp;</td><?php endforeach;?></tr><?php endfor;?></tbody></table>
      <div class="sign"><div>Personel Pengamanan Penerbangan,<div class="signspace"></div><span class="signline"><?=e($s['creator']?:'')?></span></div></div>
    </div>
  <?php else: ?>
    <div class="page std-page"><div class="print-title"><?=e($logbook['name'])?></div><table class="meta"><tr><td>Hari / Tanggal</td><td>:</td><td><?=e($fmtDate($s['session_date']))?></td></tr><tr><td>Dinas / Regu</td><td>:</td><td><?=e($s['shift']?:'-')?></td></tr><?php foreach($headerFields as $f):?><tr><td><?=e($f['label'])?></td><td>:</td><td><?=format_value($f,$headerValues[$f['id']]??'')?></td></tr><?php endforeach;?></table>
      <?php foreach(($rows?:[['values'=>[]]]) as $r):?><div class="generic"><?php foreach($detailFields as $f):?><div class="generic-row"><div><?=e($f['label'])?></div><div><?=format_value($f,$r['values'][$f['id']]??'')?></div></div><?php endforeach;?></div><?php endforeach;?></div>
  <?php endif;?>
<?php endforeach;?>
</body></html>
