<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Login AVSEC Logbook</title><link rel="stylesheet" href="<?=e(url('public/assets/app.css'))?>"></head><body class="login-page">
<div class="login-card login-card-wide">
  <div class="brand center"><div class="brand-mark">A</div><div><b>AVSEC LOGBOOK</b><small>Digital Logbook System</small></div></div>
  <h2>Masuk</h2><p class="muted">Admin dan Supervisor menggunakan username/password. Petugas dapat menggunakan password atau QR Code.</p>
  <?php if($m=flash('error')):?><div class="alert danger"><?=e($m)?></div><?php endif;?>
  <div class="login-methods">
    <section class="login-method">
      <h3>Username & Password</h3>
      <form method="post" action="<?=e(url('login'))?>"><?=csrf_field()?>
        <label class="form-label">Username</label><input class="form-control" name="username" autocomplete="username" required>
        <label class="form-label mt">Password</label><input class="form-control" type="password" name="password" autocomplete="current-password" required>
        <button class="btn btn-primary full mt-lg" type="submit">Masuk</button>
      </form>
    </section>
    <section class="login-method">
      <h3>QR Code Petugas</h3>
      <p class="muted small">Scan QR Code menggunakan kamera HP atau QR scanner USB/Bluetooth.</p>
      <form id="qrLoginForm" method="post" action="<?=e(url('login/qr'))?>"><?=csrf_field()?>
        <label class="form-label">Kode QR</label><input id="qrInput" class="form-control qr-input" name="qr" autocomplete="off" autocapitalize="characters" placeholder="Scan QR Code di sini" required>
        <button class="btn btn-primary full mt" type="submit">Login QR Code</button>
      </form>
      <button id="startQrCamera" class="btn full mt" type="button">Scan QR Code dengan Kamera</button>
      <div id="qrCameraBox" class="qr-camera-box" hidden>
        <video id="qrVideo" playsinline muted></video>
        <canvas id="qrCanvas" hidden></canvas>
        <div id="qrCameraStatus" class="form-text">Arahkan kamera ke QR Code Petugas.</div>
        <button id="stopQrCamera" class="btn btn-sm full mt" type="button">Tutup Kamera</button>
      </div>
      <div class="form-text mt">Kamera memerlukan HTTPS dan izin kamera. Sistem memakai pemindai bawaan browser jika tersedia, lalu otomatis memakai pemindai QR JavaScript sebagai cadangan.</div>
    </section>
  </div>
</div>
<!-- Fallback pemindai QR untuk browser yang tidak menyediakan BarcodeDetector. -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
(function(){
 const input=document.getElementById('qrInput'), form=document.getElementById('qrLoginForm'), start=document.getElementById('startQrCamera'), stop=document.getElementById('stopQrCamera'), box=document.getElementById('qrCameraBox'), video=document.getElementById('qrVideo'), canvas=document.getElementById('qrCanvas'), status=document.getElementById('qrCameraStatus');
 const ctx=canvas ? canvas.getContext('2d',{willReadFrequently:true}) : null;
 let stream=null, running=false, detector=null, mode='', last=0, submitting=false;
 const qrPattern=/^AVSEC-LOGIN:[A-Fa-f0-9]{32}$/;

 function submitQr(raw){
   const v=(raw||'').trim();
   if(!qrPattern.test(v)) return false;
   if(submitting) return true;
   submitting=true;
   input.value=v;
   shutdown(false).finally(()=>form.submit());
   return true;
 }

 input?.addEventListener('input',()=>submitQr(input.value));

 async function shutdown(hide=true){
   running=false;
   detector=null;
   mode='';
   if(stream){stream.getTracks().forEach(t=>t.stop());stream=null;}
   if(video){video.pause();video.srcObject=null;}
   if(hide && box) box.hidden=true;
 }

 async function prepareNativeDetector(){
   if(!('BarcodeDetector' in window)) return false;
   try{
     if(typeof BarcodeDetector.getSupportedFormats==='function'){
       const formats=await BarcodeDetector.getSupportedFormats();
       if(Array.isArray(formats) && !formats.includes('qr_code')) return false;
     }
     detector=new BarcodeDetector({formats:['qr_code']});
     return true;
   }catch(e){ detector=null; return false; }
 }

 function scanWithJsQr(){
   if(typeof window.jsQR!=='function' || !ctx || !video.videoWidth || !video.videoHeight) return null;
   const maxWidth=720;
   const scale=Math.min(1,maxWidth/video.videoWidth);
   const w=Math.max(1,Math.round(video.videoWidth*scale));
   const h=Math.max(1,Math.round(video.videoHeight*scale));
   if(canvas.width!==w || canvas.height!==h){canvas.width=w;canvas.height=h;}
   ctx.drawImage(video,0,0,w,h);
   const image=ctx.getImageData(0,0,w,h);
   const code=window.jsQR(image.data,w,h,{inversionAttempts:'attemptBoth'});
   return code && code.data ? code.data : null;
 }

 async function tick(ts){
   if(!running) return;
   if(ts-last>180){
     last=ts;
     try{
       let raw=null;
       if(mode==='native' && detector){
         const codes=await detector.detect(video);
         if(codes&&codes[0]&&codes[0].rawValue) raw=codes[0].rawValue;
       } else if(mode==='jsqr') {
         raw=scanWithJsQr();
       }
       if(raw){
         if(submitQr(raw)) return;
         status.textContent='QR Code terbaca, tetapi formatnya bukan QR login Petugas.';
       }
     }catch(e){
       // Satu frame gagal tidak menghentikan kamera. Frame berikutnya tetap diproses.
     }
   }
   requestAnimationFrame(tick);
 }

 start?.addEventListener('click',async()=>{
   if(running) return;
   box.hidden=false;
   status.textContent='Membuka kamera...';

   if(!window.isSecureContext){
     status.textContent='Kamera diblokir karena situs belum HTTPS. Buka aplikasi melalui https:// lalu coba lagi.';
     return;
   }
   if(!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia!=='function'){
     status.textContent='Browser tidak menyediakan akses kamera. Perbarui browser atau gunakan QR scanner fisik.';
     return;
   }

   try{
     const useNative=await prepareNativeDetector();
     if(useNative){ mode='native'; }
     else if(typeof window.jsQR==='function'){ mode='jsqr'; }
     else {
       status.textContent='Modul pemindai QR cadangan gagal dimuat. Periksa koneksi internet browser lalu muat ulang halaman.';
       return;
     }

     stream=await navigator.mediaDevices.getUserMedia({
       audio:false,
       video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:720}}
     });
     video.srcObject=stream;
     await video.play();
     running=true;
     status.textContent=mode==='native'
       ? 'Arahkan kamera ke QR Code Petugas.'
       : 'Arahkan kamera ke QR Code Petugas. Mode kompatibilitas aktif.';
     requestAnimationFrame(tick);
   }catch(e){
     await shutdown(false);
     const name=e && e.name ? e.name : '';
     if(name==='NotAllowedError' || name==='PermissionDeniedError') status.textContent='Izin kamera ditolak. Izinkan akses kamera pada pengaturan situs browser lalu coba lagi.';
     else if(name==='NotFoundError' || name==='DevicesNotFoundError') status.textContent='Kamera tidak ditemukan pada perangkat ini.';
     else if(name==='NotReadableError' || name==='TrackStartError') status.textContent='Kamera sedang dipakai aplikasi lain atau tidak dapat dibuka.';
     else status.textContent='Kamera tidak dapat dibuka. Pastikan HTTPS aktif dan izin kamera diberikan.';
   }
 });

 stop?.addEventListener('click',()=>shutdown(true));
 window.addEventListener('pagehide',()=>shutdown(false));
 document.addEventListener('visibilitychange',()=>{ if(document.hidden && running) shutdown(true); });
})();
</script>
</body></html>
