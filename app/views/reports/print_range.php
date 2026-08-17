<div class="card narrow">
  <div class="card-head"><div><h3>Cetak Semua Hasil Per Logbook</h3><p class="muted">Pilih satu jenis logbook dan rentang tanggal. Sistem akan menampilkan seluruh sesi dan seluruh baris pengisian dalam satu hasil cetak.</p></div></div>
  <form method="get" action="<?=e(url('print/range'))?>" target="_blank">
    <label class="form-label">Jenis Logbook *</label>
    <select class="form-select" name="logbook_id" required>
      <option value="">-- pilih logbook --</option>
      <?php foreach($logbooks as $l):?><option value="<?=$l['id']?>" <?=$logbookId===$l['id']?'selected':''?>><?=e($l['code'].' - '.$l['name'])?></option><?php endforeach;?>
    </select>
    <div class="grid2 mt">
      <div><label class="form-label">Tanggal Awal *</label><input class="form-control" type="date" name="date_from" value="<?=e($dateFrom)?>" required></div>
      <div><label class="form-label">Tanggal Akhir *</label><input class="form-control" type="date" name="date_to" value="<?=e($dateTo)?>" required></div>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Tampilkan Semua Hasil Cetak</button></div>
  </form>
</div>
