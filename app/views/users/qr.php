<div class="card narrow qr-card">
  <div class="card-head"><div><h3>QR Code Login Petugas</h3><p class="muted">QR Code adalah kredensial login. Cetak dan serahkan hanya kepada petugas yang bersangkutan.</p></div><div class="actions"><a class="btn" href="<?=e(url('users'))?>">Kembali</a></div></div>
  <div class="qr-person"><b><?=e($user['name'])?></b><span>@<?=e($user['username'])?></span></div>
  <?php if($token!==''):?>
    <div class="qr-print-area">
      <div class="qr-title">AVSEC LOGBOOK</div>
      <div class="qr-name"><?=e($user['name'])?></div>
      <div class="qr-image"><?=qr_code_svg('AVSEC-LOGIN:'.$token,7,4)?></div>
      <div class="qr-caption">QR CODE LOGIN PETUGAS</div>
    </div>
    <div class="alert success mt">QR Code baru siap digunakan. QR Code sebelumnya sudah tidak berlaku.</div>
    <div class="form-actions"><button class="btn btn-primary" type="button" onclick="window.print()">Cetak QR Code</button><a class="btn" href="<?=e(url('assignments'))?>">Atur Penugasan Petugas</a></div>
  <?php else:?>
    <div class="alert danger">Kode asli QR tidak disimpan dalam bentuk yang dapat dibaca ulang. Jika kartu QR hilang atau belum dicetak, buat QR Code baru.</div>
    <form method="post" action="<?=e(url('users/'.$user['id'].'/qr'))?>"><?=csrf_field()?><button class="btn btn-primary" type="submit">Buat / Reset QR Code</button></form>
  <?php endif;?>
</div>
