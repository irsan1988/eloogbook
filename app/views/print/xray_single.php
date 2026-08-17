<?php
$headerByKey=xray_field_by_key($headerFields);
$detailByKey=xray_field_by_key($detailFields);
$hv=function(string $key) use($headerByKey,$headerValues):string {
    return isset($headerByKey[$key])?(string)($headerValues[$headerByKey[$key]['id']]??''):'';
};
$map=xray_single_checkbox_map();
[$titleLine1,$titleLine2]=xray_single_header_lines((string)($session['code']??'XRAY-SINGLE-BAGASI'));
$printRows=$rows ?: [['sequence_no'=>1,'values'=>[],'creator'=>'']];
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?></title>
<style>
@page{size:A4 portrait;margin:0}*{box-sizing:border-box}html,body{margin:0;padding:0;background:#e5e7eb;font-family:"Times New Roman",serif}.toolbar{position:sticky;top:0;z-index:50;background:#111827;color:#fff;padding:10px 14px;display:flex;gap:8px;justify-content:center}.toolbar a,.toolbar button{border:0;border-radius:7px;padding:8px 15px;font:600 14px Arial,sans-serif;cursor:pointer;text-decoration:none;background:#fff;color:#111827}.page{position:relative;width:210mm;height:297mm;margin:10mm auto;background:#fff;overflow:hidden;page-break-after:always;box-shadow:0 2px 14px rgba(0,0,0,.18)}.page:last-child{page-break-after:auto}.template{position:absolute;inset:0;width:100%;height:100%;display:block}.template-title{position:absolute;z-index:4;left:12.86%;top:9.11%;width:74.18%;height:2.48%;background:#d9d9d9;display:flex;flex-direction:column;align-items:center;justify-content:center;border-top:.25mm solid #333;border-bottom:.25mm solid #333;font-weight:700;font-size:4mm;line-height:1.02;text-align:center}.template-title .line1{font-style:italic}.template-title .sv{font-style:italic}.tick{position:absolute;z-index:5;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;font-weight:900;font-size:4.2mm;line-height:1;color:#111}.tick.result{font-size:3.7mm}.overlay{position:absolute;z-index:4;display:flex;align-items:center;color:#111;font-family:"Times New Roman",serif;font-size:3.25mm;line-height:1;white-space:nowrap;overflow:hidden}.overlay.operator{left:37.415%;top:13.2691%;width:51.1716%;height:1.4981%}.overlay.date{left:37.415%;top:14.8208%;width:51.1716%;height:1.5516%}.overlay.lokasi{left:37.415%;top:16.4259%;width:51.1716%;height:1.4981%}.overlay.mesin{left:37.415%;top:17.9775%;width:51.1716%;height:1.4981%}.overlay.sertifikat{left:37.415%;top:19.5292%;width:51.1716%;height:1.5516%}.note-value{position:absolute;z-index:4;left:20.5%;top:69.55%;width:66%;min-height:2.45%;font-size:3mm;line-height:1.2;white-space:normal}.person1,.person2{position:absolute;z-index:4;left:16.7%;width:27%;height:2.1%;font-size:3.1mm;display:flex;align-items:flex-end;background:#fff}.person1{top:78.65%}.person2{top:84.62%}.signature1{position:absolute;z-index:6;left:68.5%;top:76.9%;width:17.5%;height:4.4%;object-fit:contain;background:transparent}
@media print{html,body{background:#fff}.toolbar{display:none!important}.page{margin:0;box-shadow:none}}
</style></head><body>
<div class="toolbar"><a href="<?=e(url('entries/session/'.$session['id']))?>">Kembali</a><button onclick="window.print()">CETAK</button></div>
<?php foreach($printRows as $r):
    $rv=function(string $key) use($detailByKey,$r):string { return isset($detailByKey[$key])?(string)($r['values'][$detailByKey[$key]['id']]??''):''; };
?>
<div class="page">
  <img class="template" src="<?=e(url('public/assets/templates/xray-single-view-template.png'))?>" alt="Checklist X-Ray Single View">
  <div class="template-title"><div class="line1"><?=e($titleLine1)?></div><div><?=e(preg_replace('/\s+SINGLE VIEW$/','',$titleLine2))?> <span class="sv">SINGLE VIEW</span></div></div>
  <div class="overlay operator"><?=e($hv('operator'))?></div>
  <div class="overlay date"><?=e(xray_date_id($session['session_date']).($hv('waktu_pengujian')!==''?' / '.$hv('waktu_pengujian').' WIB':''))?></div>
  <div class="overlay lokasi"><?=e($hv('lokasi'))?></div>
  <div class="overlay mesin"><?=e($hv('mesin'))?></div>
  <div class="overlay sertifikat"><?=e($hv('sertifikat'))?></div>
  <?php foreach($map as $key=>$pos): if($rv($key)!=='1') continue; ?>
    <div class="tick <?=$key==='result_pass'||$key==='result_fail'?'result':''?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%;width:<?=$pos[2]?>%;height:<?=$pos[3]?>%;">✓</div>
  <?php endforeach;?>
  <?php if(trim($rv('catatan'))!==''):?><div class="note-value"><?=nl2br(e($rv('catatan')))?></div><?php endif;?>
  <?php if(trim($rv('personel_1'))!==''):?><div class="person1"><?=e($rv('personel_1'))?></div><?php endif;?>
  <?php $sig1=xray_signature_src($rv('ttd_personel_1')); if($sig1!==''):?><img class="signature1" src="<?=e($sig1)?>" alt="Tanda tangan Personel 1"><?php endif;?>
  <?php if(trim($rv('personel_2'))!==''):?><div class="person2"><?=e($rv('personel_2'))?></div><?php endif;?>
</div>
<?php endforeach;?>
</body></html>
