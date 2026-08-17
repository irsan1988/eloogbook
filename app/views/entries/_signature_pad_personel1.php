<?php
if(isset($detailByKey['ttd_personel_1'])):
    $sigField=$detailByKey['ttd_personel_1'];
    $sigValue=xray_signature_src($values[$sigField['id']]??'');
    $sigId='sig_personel1_'.(int)$sigField['id'];
    $hiddenId='sig_hidden_'.(int)$sigField['id'];
?>
<div class="signature-panel">
  <label class="form-label" for="<?=$sigId?>">Tanda Tangan Personel Pengamanan Penerbangan 1</label>
  <div class="signature-pad-box">
    <canvas id="<?=$sigId?>" width="720" height="220" data-signature-pad data-hidden-id="<?=$hiddenId?>" aria-label="Area tanda tangan Personel Pengamanan Penerbangan 1"></canvas>
  </div>
  <input type="hidden" id="<?=$hiddenId?>" name="f[<?=$sigField['id']?>]" value="<?=e($sigValue)?>">
  <div class="signature-actions">
    <button type="button" class="btn btn-sm" data-signature-clear>Hapus Tanda Tangan</button>
    <span class="form-text">Tanda tangan langsung dengan mouse, stylus, atau jari.</span>
  </div>
</div>
<script>
(function(){
  const canvas=document.getElementById(<?=json_encode($sigId)?>);
  const hidden=document.getElementById(<?=json_encode($hiddenId)?>);
  if(!canvas||!hidden) return;
  const ctx=canvas.getContext('2d');
  const preview=document.querySelector('[data-signature-preview]');
  let drawing=false, hasInk=false;

  function point(ev){
    const r=canvas.getBoundingClientRect();
    return {
      x:(ev.clientX-r.left)*(canvas.width/r.width),
      y:(ev.clientY-r.top)*(canvas.height/r.height)
    };
  }
  function setup(){
    ctx.lineWidth=3.2;
    ctx.lineCap='round';
    ctx.lineJoin='round';
    ctx.strokeStyle='#111';
  }
  function sync(){
    if(!hasInk){ hidden.value=''; if(preview){preview.removeAttribute('src');preview.style.display='none';} return; }
    const data=canvas.toDataURL('image/png');
    hidden.value=data;
    if(preview){preview.src=data;preview.style.display='block';}
  }
  function clearPad(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    setup(); hasInk=false; sync();
  }

  setup();
  if(hidden.value){
    const img=new Image();
    img.onload=function(){ ctx.clearRect(0,0,canvas.width,canvas.height); ctx.drawImage(img,0,0,canvas.width,canvas.height); setup(); hasInk=true; if(preview){preview.src=hidden.value;preview.style.display='block';} };
    img.src=hidden.value;
  }

  canvas.addEventListener('pointerdown',function(ev){
    ev.preventDefault(); canvas.setPointerCapture(ev.pointerId); drawing=true; hasInk=true;
    const p=point(ev); ctx.beginPath(); ctx.moveTo(p.x,p.y);
  });
  canvas.addEventListener('pointermove',function(ev){
    if(!drawing) return; ev.preventDefault(); const p=point(ev); ctx.lineTo(p.x,p.y); ctx.stroke();
  });
  function end(ev){
    if(!drawing) return; ev.preventDefault(); drawing=false; try{canvas.releasePointerCapture(ev.pointerId);}catch(e){} sync();
  }
  canvas.addEventListener('pointerup',end);
  canvas.addEventListener('pointercancel',end);
  canvas.addEventListener('pointerleave',function(ev){ if(drawing) end(ev); });
  const clearBtn=canvas.closest('.signature-panel').querySelector('[data-signature-clear]');
  if(clearBtn) clearBtn.addEventListener('click',clearPad);
  const form=canvas.closest('form');
  if(form) form.addEventListener('submit',function(){ if(hasInk) sync(); });
})();
</script>
<?php endif; ?>
