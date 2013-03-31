<?php if (!defined('APPLICATION')) exit();

require_once($this->GetView('statusmodule.php'));

class displaymodule extends Gdn_Module {
  protected $_TSServer;

  public function GetData() {
  }

  public function getTSServer(){
    return $this->_TSServer;
  }

  public function AssetTarget() {
    return 'Panel';
  }

  public function ToString() {
    $String = '';
    $Session = Gdn::Session();
    $tsHost = C('TSServer.IP');
    $tsID = C('TSServer.ID');
    $tsQueryPort = C('TSServer.QueryPort');
    $tsQueryUser = C('TSServer.QueryUser');
    $tsQueryPass = C('TSServer.QueryPass');

    $ts = new TSStatus($tsHost, $tsQueryPort, $tsID);
    $ts->imagePath = Gdn_Url::WebRoot(TRUE).'/plugins/TSServer/design/images/';

    $ts->showNicknameBox = C('TSServer.ShowNicknameBox');
    $ts->showPasswordBox = C('TSServer.ShowPasswordBox');
    $ts->decodeUTF8 = C('TSServer.DecodeUTF8');
    $ts->timeout = C('TSServer.Timeout');
    $ts->setCache(C('TSServer.Cache'), C('TSServer.CacheFile'));
    $ts->setLoginPassword($tsQueryUser, $tsQueryPass);

    ob_start();

    if (C('TSServer.DisplaySignedInOnly')) {
      if (Gdn::Session()->IsValid()) { echo "<div class=\"Box TSServer\">".$ts->render()."</div>"; }
    } else { echo "<div class=\"Box TSServer\">".$ts->render()."</div>"; }

    $String = ob_get_contents();
    @ob_end_clean();
    return $String;
  }
}
