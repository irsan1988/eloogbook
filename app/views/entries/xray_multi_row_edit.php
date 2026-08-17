<div class="card">
  <div class="card-head"><div><h3>Edit Daily Check X-Ray</h3><div class="muted"><?=e($session['logbook_name'])?> • <?=e(xray_date_id($session['session_date']))?></div></div></div>
  <form method="post" action="<?=e(url('entries/row/'.$row['id'].'/update'))?>">
    <?=csrf_field()?>
    <?php
      $logbook=['id'=>$session['logbook_id'],'code'=>$session['code'],'name'=>$session['logbook_name']];
      $detailFields=$fields;
      $detailValues=$row['values']??[];
      $headerFields=$headerFields??[];
      $headerValues=$headerValues??[];
      require APP_ROOT.'/app/views/entries/_xray_daily_full_template.php';
    ?>
    <div class="form-actions">
      <a class="btn" href="<?=e(url('entries/session/'.$session['id']))?>">Batal</a>
      <button class="btn btn-primary">Simpan Perubahan</button>
    </div>
  </form>
</div>
