<?php
$code=(string)($logbook['code']??$session['code']??'XRAY-SINGLE-BAGASI');
$isSingle=is_xray_single_code($code);
$template=$isSingle?'xray-single-view-template.png':'xray-multi-view-template.png';
$map=$isSingle?xray_single_checkbox_map():xray_multi_checkbox_map();
[$titleLine1,$titleLine2]=$isSingle?xray_single_header_lines($code):xray_multi_header_lines($code);
$headerByKey=xray_field_by_key($headerFields??[]);
$detailByKey=xray_field_by_key($detailFields??[]);
$headerValues=$headerValues??[];
$detailValues=$detailValues??[];
$sessionDate=(string)($session['session_date']??date('Y-m-d'));
$renderSelect=function(string $key,string $class) use($headerByKey,$headerValues): void {
    if(!isset($headerByKey[$key])) return;
    $f=$headerByKey[$key]; $value=(string)($headerValues[$f['id']]??'');
    $opts=array_values(array_filter(array_map('trim',explode("\n",(string)($f['options']??''))),static fn($x)=>$x!==''));
    if($value!=='' && !in_array($value,$opts,true)) $opts[]=$value;
    $required=(int)$f['required']===1?' required':'';
    echo '<select class="xfull-control '.e($class).'" name="f['.(int)$f['id'].']" aria-label="'.e($f['label']).'"'.$required.'><option value="">-- pilih --</option>';
    foreach($opts as $o) echo '<option value="'.e($o).'"'.($o===$value?' selected':'').'>'.e($o).'</option>';
    echo '</select>';
};
$sigField=$detailByKey['ttd_personel_1']??null;
$signatureValue=$sigField?xray_signature_src($detailValues[$sigField['id']]??''):'';
$catatanField=$detailByKey['catatan']??null;
$personel1Field=$detailByKey['personel_1']??null;
$personel2Field=$detailByKey['personel_2']??null;
$sigCanvasId=$sigField?'xfull_sig_canvas_'.(int)$sigField['id']:'';
$sigHiddenId=$sigField?'xfull_sig_hidden_'.(int)$sigField['id']:'';
?>
<style>
.xfull-wrap{max-width:980px;margin:0 auto}.xfull-note{margin:0 0 10px}.xfull-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:8px}.xfull-shell{overflow:auto;padding:10px;background:#eef2f7;border:1px solid #d7dee8;border-radius:12px;-webkit-overflow-scrolling:touch}.xfull-sheet{position:relative;width:min(900px,100%);aspect-ratio:<?=$isSingle?'1323/1869':'2480/3505'?>;margin:0 auto;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08)}.xfull-sheet>img:first-child{position:absolute;inset:0;width:100%;height:100%;display:block}.xfull-title{position:absolute;z-index:4;left:12.86%;top:9.11%;width:74.18%;height:2.48%;background:#d9d9d9;display:flex;flex-direction:column;align-items:center;justify-content:center;border-top:1px solid #333;border-bottom:1px solid #333;font-family:"Times New Roman",serif;font-weight:700;font-size:clamp(9px,1.35vw,17px);line-height:1.02;text-align:center;color:#111}.xfull-title .line1,.xfull-title .view{font-style:italic}.xfull-control{position:absolute;z-index:10;margin:0;border:1px solid #2563eb;border-radius:3px;background:rgba(255,255,255,.96);color:#111;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px);line-height:1;padding:0 20px 0 3px;min-height:0;box-sizing:border-box}.xfull-control:focus{outline:2px solid rgba(37,99,235,.22);outline-offset:1px}.xfull-operator{left:37.415%;top:13.14%;width:50%;height:1.82%}.xfull-lokasi{left:37.415%;top:16.30%;width:50%;height:1.82%}.xfull-mesin{left:37.415%;top:17.86%;width:50%;height:1.82%}.xfull-sertifikat{left:37.415%;top:19.41%;width:50%;height:1.82%}.xfull-date{position:absolute;z-index:8;left:37.415%;top:14.84%;width:20%;height:1.45%;display:flex;align-items:center;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px);white-space:nowrap;overflow:hidden}.xfull-time{left:58.5%;top:14.70%;width:15%;height:1.82%;padding-right:2px}.xfull-wib{position:absolute;z-index:8;left:74%;top:14.84%;width:8%;height:1.45%;display:flex;align-items:center;font-family:"Times New Roman",serif;font-size:clamp(8px,1.05vw,14px)}.xfull-hit{position:absolute;display:block;cursor:pointer;z-index:7}.xfull-hit input[type=checkbox]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;margin:0}.xfull-hit .tick{position:absolute;inset:-8%;display:none;align-items:center;justify-content:center;font-family:Arial,sans-serif;font-weight:900;font-size:clamp(11px,1.55vw,20px);line-height:1;color:#111;pointer-events:none}.xfull-hit input[type=checkbox]:checked+.tick{display:flex}.xfull-hit:hover{outline:2px solid #2563eb;background:rgba(37,99,235,.08)}.xfull-direct{position:absolute;z-index:11;margin:0;border:1px dashed rgba(37,99,235,.65);border-radius:3px;background:rgba(255,255,255,.90);color:#111;font-family:"Times New Roman",serif;line-height:1.05;padding:1px 4px;outline:none;box-shadow:none}.xfull-direct:focus{border:1px solid #2563eb;background:#fff;box-shadow:0 0 0 2px rgba(37,99,235,.14)}.xfull-direct::placeholder{color:#64748b;opacity:.72}
<?php if($isSingle):?>
.xfull-catatan{left:20.5%;top:69.55%;width:66%;height:2.45%;font-size:clamp(7px,1.02vw,14px)}.xfull-person1{left:16.7%;top:78.65%;width:27%;height:2.1%;font-size:clamp(7px,1.05vw,14px)}.xfull-person2{left:16.7%;top:84.62%;width:27%;height:2.1%;font-size:clamp(7px,1.05vw,14px)}.xfull-signature{left:68.5%;top:76.9%;width:17.5%;height:4.4%}
<?php else:?>
.xfull-catatan{left:20.5%;top:81.25%;width:66%;height:1.75%;font-size:clamp(7px,1vw,14px)}.xfull-person1{left:16.7%;top:85.0%;width:27%;height:1.75%;font-size:clamp(7px,1vw,14px)}.xfull-person2{left:16.7%;top:88.62%;width:27%;height:1.75%;font-size:clamp(7px,1vw,14px)}.xfull-signature{left:68.5%;top:84.15%;width:17.5%;height:3.35%}
<?php endif;?>
.xfull-signature{position:absolute;z-index:12;border:1px dashed rgba(37,99,235,.65);border-radius:4px;background:rgba(255,255,255,.04);overflow:hidden}.xfull-signature canvas{display:block;width:100%;height:100%;touch-action:none;cursor:crosshair;background:transparent}.xfull-signature.is-active{border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.14)}.xfull-footer{display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;margin-top:10px}.xfull-key{display:flex;gap:18px;flex-wrap:wrap;font-size:13px}.xfull-key b{font-size:16px}
@media(max-width:700px){.xfull-shell{padding:3px}.xfull-shell.is-zoomed .xfull-sheet{width:<?=$isSingle?'780':'820'?>px;max-width:none}.xfull-control,.xfull-date,.xfull-wib,.xfull-title{font-size:10px}.xfull-shell.is-zoomed .xfull-direct{font-size:11px}.xfull-direct{font-size:clamp(5px,1.7vw,10px);padding:0 2px}.xfull-hit .tick{font-size:clamp(8px,2.8vw,15px)}.xfull-footer{align-items:stretch}.xfull-footer .btn{flex:1 1 auto}}
</style>
<div class="xfull-wrap" data-xfull-root>
  <div class="alert success xfull-note"><b>Satu lembar, satu form.</b> Dropdown header, checkbox pengujian, Catatan, Personel Pengamanan Penerbangan, dan tanda tangan Personel 1 diisi langsung pada lembar template yang sama.</div>
  <div class="xfull-tools"><button type="button" class="btn btn-sm" data-xfull-zoom>Perbesar Template</button><span class="muted small">Di HP, gunakan Perbesar Template agar kotak checkbox dan area tanda tangan lebih mudah digunakan.</span></div>
  <div class="xfull-shell" data-xfull-shell>
    <div class="xfull-sheet">
      <img src="<?=e(url('public/assets/templates/'.$template))?>" alt="Template Daily Check X-Ray">
      <div class="xfull-title"><div class="line1"><?=e($titleLine1)?></div><div><?=e(preg_replace('/\s+(SINGLE|MULTI) VIEW$/','',$titleLine2))?> <span class="view"><?=$isSingle?'SINGLE VIEW':'MULTI VIEW'?></span></div></div>
      <?php $renderSelect('operator','xfull-operator'); ?>
      <div class="xfull-date" data-xfull-date><?=e(xray_date_id($sessionDate))?></div>
      <?php if(isset($headerByKey['waktu_pengujian'])): $wf=$headerByKey['waktu_pengujian'];?><input class="xfull-control xfull-time" type="time" name="f[<?=$wf['id']?>]" value="<?=e($headerValues[$wf['id']]??'')?>" <?=((int)$wf['required']===1)?'required':''?> aria-label="Waktu Pengujian"><?php endif;?>
      <div class="xfull-wib">/ WITA</div>
      <?php $renderSelect('lokasi','xfull-lokasi'); ?>
      <?php $renderSelect('mesin','xfull-mesin'); ?>
      <?php $renderSelect('sertifikat','xfull-sertifikat'); ?>

      <?php foreach($map as $key=>$pos): if(!isset($detailByKey[$key])) continue; $f=$detailByKey[$key]; $checked=((string)($detailValues[$f['id']]??'')==='1'); ?>
        <label class="xfull-hit" title="<?=e($f['label'])?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%;width:<?=$pos[2]?>%;height:<?=$pos[3]?>%;">
          <input type="hidden" name="f[<?=$f['id']?>]" value="0">
          <input type="checkbox" name="f[<?=$f['id']?>]" value="1" <?=$checked?'checked':''?> <?=$key==='result_pass'||$key==='result_fail'?'data-xfull-result="'.e($key).'"':''?>>
          <span class="tick">✓</span>
        </label>
      <?php endforeach;?>
      <?php if($catatanField):?><input class="xfull-direct xfull-catatan" type="text" name="f[<?=$catatanField['id']?>]" value="<?=e($detailValues[$catatanField['id']]??'')?>" placeholder="Isi catatan" aria-label="Catatan"><?php endif;?>
      <?php if($personel1Field):?><input class="xfull-direct xfull-person1" type="text" name="f[<?=$personel1Field['id']?>]" value="<?=e($detailValues[$personel1Field['id']]??'')?>" placeholder="Nama Personel 1" aria-label="Personel Pengamanan Penerbangan 1"><?php endif;?>
      <?php if($personel2Field):?><input class="xfull-direct xfull-person2" type="text" name="f[<?=$personel2Field['id']?>]" value="<?=e($detailValues[$personel2Field['id']]??'')?>" placeholder="Nama Personel 2" aria-label="Personel Pengamanan Penerbangan 2"><?php endif;?>
      <?php if($sigField):?>
        <div class="xfull-signature" data-xfull-signature-wrap title="Tanda tangan Personel Pengamanan Penerbangan 1">
          <canvas id="<?=e($sigCanvasId)?>" width="700" height="210" data-xfull-signature aria-label="Area tanda tangan Personel Pengamanan Penerbangan 1"></canvas>
        </div>
        <input type="hidden" id="<?=e($sigHiddenId)?>" name="f[<?=$sigField['id']?>]" value="<?=e($signatureValue)?>">
      <?php endif;?>
    </div>
  </div>
  <div class="xfull-footer">
    <div class="xfull-key"><span><b>✓</b> Terpenuhi</span><span>PASS dan FAIL hanya dapat dipilih salah satu.</span></div>
    <?php if($sigField):?><button type="button" class="btn btn-sm" data-xfull-signature-clear>Hapus Tanda Tangan</button><?php endif;?>
  </div>
</div>
<script>
(function(){
  const root=document.currentScript.previousElementSibling;
  if(!root)return;
  const shell=root.querySelector('[data-xfull-shell]');
  const zoom=root.querySelector('[data-xfull-zoom]');
  if(zoom&&shell)zoom.addEventListener('click',()=>{const on=shell.classList.toggle('is-zoomed');zoom.textContent=on?'Kecilkan Template':'Perbesar Template';if(on)shell.scrollLeft=Math.max(0,(shell.scrollWidth-shell.clientWidth)/2);});
  const dateInput=document.querySelector('input[name="session_date"]');
  const dateOut=root.querySelector('[data-xfull-date]');
  const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  function syncDate(){if(!dateInput||!dateOut)return;const m=/^(\d{4})-(\d{2})-(\d{2})$/.exec(dateInput.value);if(!m){dateOut.textContent=dateInput.value;return;}dateOut.textContent=Number(m[3])+' '+months[Number(m[2])-1]+' '+m[1];}
  if(dateInput){dateInput.addEventListener('change',syncDate);dateInput.addEventListener('input',syncDate);syncDate();}
  const pass=root.querySelector('[data-xfull-result="result_pass"]');
  const fail=root.querySelector('[data-xfull-result="result_fail"]');
  if(pass&&fail){pass.addEventListener('change',()=>{if(pass.checked)fail.checked=false;});fail.addEventListener('change',()=>{if(fail.checked)pass.checked=false;});}
  const canvas=root.querySelector('[data-xfull-signature]');
  const hiddenId=<?=json_encode($sigField?$sigHiddenId:'')?>;
  const hidden=hiddenId?document.getElementById(hiddenId):null;
  if(canvas&&hidden){
    const ctx=canvas.getContext('2d');const wrap=canvas.closest('[data-xfull-signature-wrap]');let drawing=false,hasInk=false;
    const setup=()=>{ctx.lineWidth=3.2;ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#111';};
    const point=(ev)=>{const r=canvas.getBoundingClientRect();return{x:(ev.clientX-r.left)*(canvas.width/r.width),y:(ev.clientY-r.top)*(canvas.height/r.height)}};
    const sync=()=>{hidden.value=hasInk?canvas.toDataURL('image/png'):'';};
    const clear=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);setup();hasInk=false;sync();};
    setup();
    if(hidden.value){const img=new Image();img.onload=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);ctx.drawImage(img,0,0,canvas.width,canvas.height);setup();hasInk=true;};img.src=hidden.value;}
    canvas.addEventListener('pointerdown',ev=>{ev.preventDefault();canvas.setPointerCapture(ev.pointerId);drawing=true;hasInk=true;wrap&&wrap.classList.add('is-active');const p=point(ev);ctx.beginPath();ctx.moveTo(p.x,p.y);});
    canvas.addEventListener('pointermove',ev=>{if(!drawing)return;ev.preventDefault();const p=point(ev);ctx.lineTo(p.x,p.y);ctx.stroke();});
    const end=(ev)=>{if(!drawing)return;ev.preventDefault();drawing=false;wrap&&wrap.classList.remove('is-active');try{canvas.releasePointerCapture(ev.pointerId)}catch(e){}sync();};
    canvas.addEventListener('pointerup',end);canvas.addEventListener('pointercancel',end);canvas.addEventListener('pointerleave',ev=>{if(drawing)end(ev)});
    const clearBtn=root.querySelector('[data-xfull-signature-clear]');if(clearBtn)clearBtn.addEventListener('click',clear);
    const form=canvas.closest('form');if(form)form.addEventListener('submit',()=>{if(hasInk)sync();});
  }
})();
</script>
