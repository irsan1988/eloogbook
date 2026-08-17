<?php
$detailByKey=xray_field_by_key($fields);
$headerByKey=xray_field_by_key($headerFields ?? []);
$getHeader=function(string $key) use($headerByKey,$headerValues): string {
    if(!isset($headerByKey[$key])) return '';
    return (string)($headerValues[$headerByKey[$key]['id']] ?? '');
};
$map=xray_single_checkbox_map();
[$titleLine1,$titleLine2]=xray_single_header_lines((string)($session['code']??'XRAY-SINGLE-BAGASI'));
$sigField=$detailByKey['ttd_personel_1']??null;
$signatureValue=$sigField?xray_signature_src($values[$sigField['id']]??''):'';
$catatanField=$detailByKey['catatan']??null;
$personel1Field=$detailByKey['personel_1']??null;
$personel2Field=$detailByKey['personel_2']??null;
$sigCanvasId=$sigField?'xray_sig_canvas_'.(int)$sigField['id']:'';
$sigHiddenId=$sigField?'xray_sig_hidden_'.(int)$sigField['id']:'';
?>
<style>
.xray-editor{max-width:980px;margin:0 auto}.xray-template-title{position:absolute;z-index:4;left:12.86%;top:9.11%;width:74.18%;height:2.48%;background:#d9d9d9;display:flex;flex-direction:column;align-items:center;justify-content:center;border-top:1px solid #333;border-bottom:1px solid #333;font-family:"Times New Roman",serif;font-weight:700;font-size:clamp(9px,1.35vw,17px);line-height:1.02;text-align:center;color:#111}.xray-template-title .line1{font-style:italic}.xray-template-title .sv{font-style:italic}.xray-editor-note{margin-bottom:14px}.xray-sheet-shell{overflow:auto;padding:10px;background:#eef2f7;border:1px solid #d7dee8;border-radius:12px}.xray-sheet{position:relative;width:min(900px,100%);aspect-ratio:1323/1869;margin:0 auto;background:white;box-shadow:0 2px 10px rgba(0,0,0,.08)}.xray-sheet>img:first-child{position:absolute;inset:0;width:100%;height:100%;display:block}.xray-hit{position:absolute;display:block;cursor:pointer;z-index:5}.xray-hit input[type=checkbox]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;margin:0}.xray-hit .tick{position:absolute;inset:-8%;display:none;align-items:center;justify-content:center;font-family:Arial,sans-serif;font-weight:900;font-size:clamp(11px,1.55vw,20px);line-height:1;color:#111;pointer-events:none}.xray-hit input[type=checkbox]:checked+.tick{display:flex}.xray-hit:hover{outline:2px solid #2563eb;background:rgba(37,99,235,.08)}.xray-overlay-text{position:absolute;z-index:4;display:flex;align-items:center;font-family:"Times New Roman",serif;font-size:clamp(9px,1.2vw,16px);line-height:1.05;color:#111;white-space:nowrap;overflow:hidden}.xray-overlay-text.operator{left:37.415%;top:13.2691%;width:51.1716%;height:1.4981%}.xray-overlay-text.date{left:37.415%;top:14.8208%;width:51.1716%;height:1.5516%}.xray-overlay-text.lokasi{left:37.415%;top:16.4259%;width:51.1716%;height:1.4981%}.xray-overlay-text.mesin{left:37.415%;top:17.9775%;width:51.1716%;height:1.4981%}.xray-overlay-text.sertifikat{left:37.415%;top:19.5292%;width:51.1716%;height:1.5516%}
.xray-direct-field{position:absolute;z-index:8;margin:0;border:1px dashed rgba(37,99,235,.55);border-radius:3px;background:rgba(255,255,255,.88);color:#111;font-family:"Times New Roman",serif;line-height:1.05;padding:1px 4px;outline:none;box-shadow:none}.xray-direct-field:focus{border:1px solid #2563eb;background:#fff;box-shadow:0 0 0 2px rgba(37,99,235,.14)}.xray-direct-field::placeholder{color:#64748b;opacity:.72}.xray-direct-catatan{left:20.5%;top:69.55%;width:66%;height:2.45%;font-size:clamp(7px,1.02vw,14px)}.xray-direct-person1{left:16.7%;top:78.65%;width:27.0%;height:2.1%;font-size:clamp(7px,1.05vw,14px)}.xray-direct-person2{left:16.7%;top:84.62%;width:27.0%;height:2.1%;font-size:clamp(7px,1.05vw,14px)}.xray-inline-signature{position:absolute;z-index:9;left:68.5%;top:76.9%;width:17.5%;height:4.4%;border:1px dashed rgba(37,99,235,.55);border-radius:4px;background:rgba(255,255,255,.04);overflow:hidden}.xray-inline-signature canvas{display:block;width:100%;height:100%;touch-action:none;cursor:crosshair;background:transparent}.xray-inline-signature.is-active{border-color:#2563eb;box-shadow:0 0 0 2px rgba(37,99,235,.14)}.xray-actions{display:flex;justify-content:flex-end;gap:10px;align-items:center;flex-wrap:wrap;margin-top:12px}.xray-key{display:flex;gap:18px;flex-wrap:wrap;margin-top:10px;font-size:13px}.xray-key b{font-size:16px}.xray-mobile-tools{display:none;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 8px}.xray-mobile-tools .form-text{font-size:12px}
@media(max-width:700px){.xray-editor{max-width:100%}.xray-editor-note{font-size:12px;line-height:1.45;margin-bottom:10px}.xray-mobile-tools{display:flex}.xray-sheet-shell{padding:3px;overflow-x:auto;-webkit-overflow-scrolling:touch;overscroll-behavior-x:contain}.xray-sheet{width:100%;min-width:0}.xray-sheet-shell.is-zoomed .xray-sheet{width:780px;max-width:none}.xray-sheet-shell.is-zoomed .xray-hit .tick{font-size:15px}.xray-sheet-shell.is-zoomed .xray-overlay-text{font-size:11px}.xray-sheet-shell.is-zoomed .xray-template-title{font-size:11px}.xray-sheet-shell.is-zoomed .xray-direct-field{font-size:11px}.xray-hit{touch-action:manipulation}.xray-hit .tick{font-size:clamp(8px,2.8vw,15px)}.xray-overlay-text{font-size:clamp(6px,1.75vw,11px)}.xray-template-title{font-size:clamp(6px,1.9vw,11px)}.xray-direct-field{font-size:clamp(5px,1.7vw,10px);padding:0 2px}.xray-inline-signature{border-width:1px}.xray-actions{align-items:stretch}.xray-actions .btn{flex:1 1 auto}}
</style>
<div class="xray-editor">
  <div class="alert success xray-editor-note"><b>Pengisian langsung pada template:</b> centang kotak TEST, isi Catatan dan Personel pada garis yang tersedia, lalu tanda tangan langsung pada area kanan Personel 1.</div>
  <form method="post" action="<?=e($action)?>">
    <?=csrf_field()?>
    <div class="xray-mobile-tools">
      <button class="btn btn-sm" type="button" data-xray-zoom>Perbesar Form</button>
      <span class="form-text">Gunakan Perbesar Form di HP agar kotak kecil, nama personel, catatan, dan tanda tangan lebih mudah diisi.</span>
    </div>
    <div class="xray-sheet-shell" data-xray-shell>
      <div class="xray-sheet">
        <img src="<?=e(url('public/assets/templates/xray-single-view-template.png'))?>" alt="Template checklist X-Ray Single View">
        <div class="xray-template-title"><div class="line1"><?=e($titleLine1)?></div><div><?=e(preg_replace('/\s+SINGLE VIEW$/','',$titleLine2))?> <span class="sv">SINGLE VIEW</span></div></div>
        <div class="xray-overlay-text operator"><?=e($getHeader('operator'))?></div>
        <div class="xray-overlay-text date"><?=e(xray_date_id($session['session_date']).($getHeader('waktu_pengujian')!==''?' / '.$getHeader('waktu_pengujian').' WIB':''))?></div>
        <div class="xray-overlay-text lokasi"><?=e($getHeader('lokasi'))?></div>
        <div class="xray-overlay-text mesin"><?=e($getHeader('mesin'))?></div>
        <div class="xray-overlay-text sertifikat"><?=e($getHeader('sertifikat'))?></div>
        <?php foreach($map as $key=>$pos): if(!isset($detailByKey[$key])) continue; $f=$detailByKey[$key]; $checked=((string)($values[$f['id']]??'')==='1'); ?>
          <label class="xray-hit" title="<?=e($f['label'])?>" style="left:<?=$pos[0]?>%;top:<?=$pos[1]?>%;width:<?=$pos[2]?>%;height:<?=$pos[3]?>%;">
            <input type="hidden" name="f[<?=$f['id']?>]" value="0">
            <input type="checkbox" name="f[<?=$f['id']?>]" value="1" <?=$checked?'checked':''?> <?=$key==='result_pass'||$key==='result_fail'?'data-result="'.e($key).'"':''?>>
            <span class="tick">✓</span>
          </label>
        <?php endforeach;?>
        <?php if($catatanField):?><input class="xray-direct-field xray-direct-catatan" type="text" name="f[<?=$catatanField['id']?>]" value="<?=e($values[$catatanField['id']]??'')?>" placeholder="Isi catatan"><?php endif;?>
        <?php if($personel1Field):?><input class="xray-direct-field xray-direct-person1" type="text" name="f[<?=$personel1Field['id']?>]" value="<?=e($values[$personel1Field['id']]??'')?>" placeholder="Nama Personel 1"><?php endif;?>
        <?php if($personel2Field):?><input class="xray-direct-field xray-direct-person2" type="text" name="f[<?=$personel2Field['id']?>]" value="<?=e($values[$personel2Field['id']]??'')?>" placeholder="Nama Personel 2"><?php endif;?>
        <?php if($sigField):?>
          <div class="xray-inline-signature" data-signature-wrap title="Tanda tangan Personel Pengamanan Penerbangan 1">
            <canvas id="<?=e($sigCanvasId)?>" width="640" height="225" data-signature-pad aria-label="Area tanda tangan Personel Pengamanan Penerbangan 1"></canvas>
          </div>
          <input type="hidden" id="<?=e($sigHiddenId)?>" name="f[<?=$sigField['id']?>]" value="<?=e($signatureValue)?>">
        <?php endif;?>
      </div>
    </div>
    <div class="xray-key"><span><b>✓</b> Terpenuhi</span><span>Catatan, nama personel, dan tanda tangan tersimpan sebagai bagian dari lembar yang sama.</span></div>
    <div class="xray-actions">
      <?php if($sigField):?><button type="button" class="btn btn-sm" data-signature-clear>Hapus Tanda Tangan</button><?php endif;?>
      <a class="btn" href="<?=e($cancelUrl)?>">Batal</a>
      <button class="btn btn-primary"><?=e($buttonLabel)?></button>
    </div>
  </form>
</div>
<script>
(function(){
  const root=document.currentScript.previousElementSibling;
  const zoomBtn=root.querySelector('[data-xray-zoom]');
  const zoomShell=root.querySelector('[data-xray-shell]');
  if(zoomBtn&&zoomShell){
    zoomBtn.addEventListener('click',function(){
      const zoomed=zoomShell.classList.toggle('is-zoomed');
      zoomBtn.textContent=zoomed?'Kecilkan Form':'Perbesar Form';
      if(zoomed) zoomShell.scrollLeft=Math.max(0,(zoomShell.scrollWidth-zoomShell.clientWidth)/2);
    });
  }
  const pass=root.querySelector('[data-result="result_pass"]');
  const fail=root.querySelector('[data-result="result_fail"]');
  if(pass&&fail){
    pass.addEventListener('change',()=>{if(pass.checked) fail.checked=false;});
    fail.addEventListener('change',()=>{if(fail.checked) pass.checked=false;});
  }
  const canvas=root.querySelector('[data-signature-pad]');
  const hiddenId=<?=json_encode($sigField?$sigHiddenId:'')?>;
  const hidden=hiddenId?document.getElementById(hiddenId):null;
  if(canvas&&hidden){
    const ctx=canvas.getContext('2d'); const wrap=canvas.closest('[data-signature-wrap]'); let drawing=false,hasInk=false;
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
    const clearBtn=root.querySelector('[data-signature-clear]');if(clearBtn)clearBtn.addEventListener('click',clear);
    const form=canvas.closest('form');if(form)form.addEventListener('submit',()=>{if(hasInk)sync();});
  }
})();
</script>
