<div class="card">
    <div class="card-head">
        <div>
            <h2><?=e($title??'Copilot Features')?></h2>
            <p class="muted">Kelola fitur bantuan Copilot secara dinamis dari database.</p>
        </div>
        <span class="badge">Terbaru</span>
    </div>

    <div class="notice">
        <strong>Catatan:</strong> halaman ini mengatur status fitur di aplikasi Eloogbook. Integrasi API GitHub/Copilot tetap mengikuti konfigurasi organisasi GitHub Anda.
    </div>

    <?php if(empty($features)): ?>
        <p class="muted">Belum ada fitur Copilot.</p>
    <?php else: ?>
        <div class="feature-list">
            <?php foreach($features as $feature): ?>
                <article class="feature-row">
                    <div>
                        <h3><?=e($feature['name'])?></h3>
                        <p><?=e($feature['description'])?></p>
                        <small class="muted">Kode: <?=e($feature['code'])?> · Diperbarui: <?=e($feature['updated_at'])?></small>
                    </div>
                    <form method="post" action="<?=e(url('settings/copilot/features/'.$feature['code'].'/toggle'))?>">
                        <?=csrf_field()?>
                        <input type="hidden" name="status" value="<?=$feature['status']==='enabled'?'disabled':'enabled'?>">
                        <button type="submit" class="btn <?=$feature['status']==='enabled'?'btn-success':'btn-secondary'?>">
                            <?=$feature['status']==='enabled'?'Aktif':'Nonaktif'?>
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px;box-shadow:0 4px 18px rgba(15,23,42,.05)}
.card-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:18px}
.card h2,.card h3{margin:0 0 6px}.card h3{font-size:1.05rem}.muted{color:#64748b}.card p{margin:4px 0 10px}.badge{background:#2563eb;color:#fff;border-radius:999px;padding:6px 11px;font-size:.78rem}.notice{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:10px;padding:12px 14px;margin-bottom:18px}.feature-list{display:grid;gap:12px}.feature-row{display:flex;justify-content:space-between;gap:18px;align-items:center;border:1px solid #e5e7eb;border-radius:10px;padding:16px}.feature-row p{color:#475569}.btn{border:0;border-radius:8px;padding:9px 13px;cursor:pointer;font-weight:600;white-space:nowrap}.btn-success{background:#16a34a;color:#fff}.btn-secondary{background:#64748b;color:#fff}@media(max-width:640px){.feature-row{align-items:flex-start;flex-direction:column}.card-head{flex-direction:column}}
</style>
