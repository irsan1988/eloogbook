<?php
class SettingsController extends Controller {
    private SettingsModel $m;

    public function __construct(){ $this->m=new SettingsModel(); }

    public function copilotFeatures(): void {
        $this->requireAdmin();
        $this->view('settings/copilot_features',[
            'title'=>'Copilot Features',
            'features'=>$this->m->copilotFeatures(),
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
    }

    public function toggleCopilotFeature(string $code): void {
        $this->requireAdmin();
        $this->csrf();
        $enabled=(($_POST['status']??'disabled')==='enabled');
        $this->m->setFeatureStatus($code,$enabled);
        audit('update','copilot_feature',0,['code'=>$code,'status'=>$enabled?'enabled':'disabled']);
        flash('success','Status fitur Copilot berhasil diperbarui.');
        $this->redirect('settings/copilot/features');
    }
}
