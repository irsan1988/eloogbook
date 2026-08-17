<?php
$code=(string)($logbook['code']??'');
$isSingle=is_xray_single_code($code);
[$titleLine1,$titleLine2]=$isSingle?xray_single_header_lines($code):xray_multi_header_lines($code);
$template=$isSingle?'xray-single-view-template.png':'xray-multi-view-template.png';
$byKey=xray_field_by_key($fields);
$getValue=function(string $key) use($byKey,$values): string {
    if(!isset($byKey[$key])) return '';
    return (string)($values[$byKey[$key]['id']]??'');
};
$renderSelect=function(string $key,string $class) use($byKey,$values): void {
    if(!isset($byKey[$key])) return;
    $f=$byKey[$key]; $value=(string)($values[$f['id']]??'');
    $opts=array_values(array_filter(array_map('trim',explode("\n",(string)($f['options']??''))),static fn($x)=>$x!==''));
    if($value!=='' && !in_array($value,$opts,true)) $opts[]=$value;
    $required=(int)$f['required']===1?' required':'';
    echo '<select class="xh-control '.e($class).'" name="f['.(int)$f['id'].']" aria-label="'.e($f['label']).'"'.$required.'><option value="">-- pilih --</option>';
    foreach($opts as $o) echo '<option value="'.e($o).'"'.($o===$value?' selected':'').'>'.e($o).'</option>';
    echo '</select>';
};
?>
<style>
.xh-wrap{max-width:980px;margin:0 auto}.xh-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:8px}.xh-shell{overflow:auto;padding:10px;background:#eef2f7;border:1px solid #d7dee8;border-radius:12px;-webkit-overflow-scrolling:touch}.xh-sheet{position:relative;width:min(900px,100%);aspect-ratio:<?=$isSingle?'1323/1869':'2480/3505'?>;margin:0 auto;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08)}.xh-sheet>img{position:absolute;inset:0;width:100%;height:100%;display:block}.xh-title{position:absolute;z-index:4;left:12.86%;top:9.11%;width:74.18%;height:2.48%;background:#d9d9d9;display:flex;flex-direction:column;align-items:center;justify-content:center;border-top:1px solid #333;border-bottom:1px solid #333;font-family:"Times New Roman",serif;font-weight:700;font-size:clamp(9px,1.35vw,17px);line-height:1.02;text-align:center;color:#111}.xh-title .line1,.xh-title .view{font-style:italic}.xh-control{position:absolute;z-index:7;margin:0;border:1px solid #2563eb;border-radius:3px;background:rgba(255,255,255,.96);color:#111;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px);line-height:1;padding:0 20px 0 3px;min-height:0;box-sizing:border-box}.xh-control:focus{outline:2px solid rgba(37,99,235,.22);outline-offset:1px}.xh-operator{left:37.415%;top:13.14%;width:50%;height:1.82%}.xh-lokasi{left:37.415%;top:16.30%;width:50%;height:1.82%}.xh-mesin{left:37.415%;top:17.86%;width:50%;height:1.82%}.xh-sertifikat{left:37.415%;top:19.41%;width:50%;height:1.82%}.xh-date{position:absolute;z-index:6;left:37.415%;top:14.84%;width:20%;height:1.45%;display:flex;align-items:center;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px);white-space:nowrap;overflow:hidden}.xh-time{left:58.5%;top:14.70%;width:15%;height:1.82%;padding-right:2px}.xh-wib{position:absolute;z-index:6;left:74%;top:14.84%;width:8%;height:1.45%;display:flex;align-items:center;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px)}.xh-help{margin-top:8px;font-size:12px;color:#64748b}
@media(max-width:700px){.xh-shell{padding:3px}.xh-shell.is-zoomed .xh-sheet{width:800px;max-width:none}.xh-control,.xh-date,.xh-wib,.xh-title{font-size:10px}.xh-tools .btn{width:auto}}
</style>
<div class="xh-wrap">
  <div class="xh-tools"><button type="button" class="btn btn-sm" data-xh-zoom>Perbesar Template</button><span class="muted small">Dropdown diisi langsung pada posisi header lembar X-Ray.</span></div>
  <div class="xh-shell" data-xh-shell>
    <div class="xh-sheet">
      <img src="<?=e(url('public/assets/templates/'.$template))?>" alt="Template header Daily Check X-Ray">
      <div class="xh-title"><div class="line1"><?=e($titleLine1)?></div><div><?=e(preg_replace('/\s+(SINGLE|MULTI) VIEW$/','',$titleLine2))?> <span class="view"><?=$isSingle?'SINGLE VIEW':'MULTI VIEW'?></span></div></div>
      <?php $renderSelect('operator','xh-operator'); ?>
      <div class="xh-date" data-xh-date><?=e(xray_date_id($session['session_date']??date('Y-m-d')))?></div>
      <?php if(isset($byKey['waktu_pengujian'])): $wf=$byKey['waktu_pengujian'];?><input class="xh-control xh-time" type="time" name="f[<?=$wf['id']?>]" value="<?=e($values[$wf['id']]??'')?>" <?=((int)$wf['required']===1)?'required':''?> aria-label="Waktu Pengujian"><?php endif;?>
      <div class="xh-wib">/ WIB</div>
      <?php $renderSelect('lokasi','xh-lokasi'); ?>
      <?php $renderSelect('mesin','xh-mesin'); ?>
      <?php $renderSelect('sertifikat','xh-sertifikat'); ?>
    </div>
  </div>
  <div class="xh-help">Jika pilihan belum tersedia, Admin menambahkannya melalui menu <b>Master Data X-Ray</b>. Nilai yang dipilih disimpan pada data logbook, sehingga hasil cetak lama tidak berubah ketika master data kemudian diedit.</div>
</div>
<script>
(function(){
  const root=document.currentScript.previousElementSibling;
  const shell=root.querySelector('[data-xh-shell]');
  const btn=root.querySelector('[data-xh-zoom]');
  if(btn&&shell)btn.addEventListener('click',()=>{const z=shell.classList.toggle('is-zoomed');btn.textContent=z?'Kecilkan Template':'Perbesar Template';});
  const dateInput=document.querySelector('input[name="session_date"]');
  const dateOut=root.querySelector('[data-xh-date]');
  const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  function syncDate(){if(!dateInput||!dateOut)return;const m=/^(\d{4})-(\d{2})-(\d{2})$/.exec(dateInput.value);if(!m){dateOut.textContent=dateInput.value;return;}dateOut.textContent=Number(m[3])+' '+months[Number(m[2])-1]+' '+m[1];}
  if(dateInput){dateInput.addEventListener('change',syncDate);dateInput.addEventListener('input',syncDate);syncDate();}
})();
</script>
