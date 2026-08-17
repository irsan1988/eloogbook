<div class="card">
  <div class="card-head">
    <div>
      <h3>Master Data DIALY CHECK HARIAN X-RAY</h3>
      <p class="muted">Empat data header Daily Check X-Ray disimpan pada tabel master tersendiri. Pada form pengisian, Petugas/Admin memilih nilainya melalui dropdown. Mengubah master data tidak mengubah hasil logbook lama yang sudah tersimpan.</p>
    </div>
  </div>
  <div class="alert success"><b>Dropdown yang memakai master data:</b> Nama Operator Penerbangan, Lokasi Penempatan/Gedung, Merk/Tipe/Nomor Seri, serta Nomor dan Tanggal Sertifikat.</div>
</div>

<div class="master-grid">
<?php foreach($types as $type=>$meta): ?>
  <section class="card" id="<?=e($type)?>">
    <div class="card-head"><div><h3><?=e($meta['label'])?></h3><div class="muted small">Dipakai pada seluruh Bagasi, Cabin, SSCP, dan Cargo, Single View maupun Multi View.</div></div></div>
    <form method="post" action="<?=e(url('xray-master/'.$type))?>" class="master-add-form">
      <?=csrf_field()?>
      <div class="master-add-row">
        <div class="master-grow"><label class="form-label">Tambah Pilihan</label><input class="form-control" name="value" required maxlength="191" placeholder="Masukkan nilai yang akan tampil di dropdown"></div>
        <div><label class="form-label">Urutan</label><input class="form-control master-order" type="number" name="sort_order" value="0"></div>
        <input type="hidden" name="active" value="1">
        <div class="master-add-button"><button class="btn btn-primary">Tambah</button></div>
      </div>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Pilihan Dropdown</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <?php foreach($items[$type]??[] as $x): ?>
      <tr>
        <td><?=e($x['value'])?></td><td><?=e($x['sort_order'])?></td><td><?=$x['active']?'<span class="badge">Aktif</span>':'<span class="muted">Nonaktif</span>'?></td>
        <td class="actions">
          <details><summary class="btn btn-sm">Edit</summary>
            <form class="inline-editor" method="post" action="<?=e(url('xray-master/'.$type.'/'.$x['id'].'/update'))?>">
              <?=csrf_field()?>
              <input class="form-control" name="value" maxlength="191" required value="<?=e($x['value'])?>">
              <input class="form-control" type="number" name="sort_order" value="<?=e($x['sort_order'])?>">
              <select class="form-select" name="active"><option value="1" <?=$x['active']?'selected':''?>>Aktif</option><option value="0" <?=!$x['active']?'selected':''?>>Nonaktif</option></select>
              <button class="btn btn-primary btn-sm">Simpan</button>
            </form>
          </details>
          <form method="post" action="<?=e(url('xray-master/'.$type.'/'.$x['id'].'/delete'))?>" style="display:inline" onsubmit="return confirm('Hapus pilihan ini dari dropdown? Data logbook lama tetap tersimpan.')">
            <?=csrf_field()?>
            <button class="btn btn-danger btn-sm">Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($items[$type])):?><tr><td colspan="4" class="muted">Belum ada pilihan. Tambahkan data agar dropdown dapat digunakan.</td></tr><?php endif;?>
    </tbody></table></div>
  </section>
<?php endforeach; ?>
</div>
<style>
.master-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.master-add-row{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap}.master-grow{flex:1 1 320px}.master-order{width:90px}.master-add-button{padding-bottom:1px}@media(max-width:900px){.master-grid{grid-template-columns:1fr}.master-add-row>*{width:100%}.master-order{width:100%}.master-add-button .btn{width:100%}}
</style>
