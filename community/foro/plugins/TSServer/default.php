<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['TSServer'] = array(
  'Name' => 'TSServer',
  'Description' => "This plugin allows you to display a Teamspeak-Viewer on your VanillaForum!\nThanks to Sebastien Gerard (sebeuu@gmail.com) http://tsstatus.sebastien.me/",
  'Version' => '1.0.3',
  'Author' => "Lukas Matt",
  'AuthorEmail' => 'lukas@zauberstuhl.de',
  'AuthorUrl' => 'http://www.zauberstuhl.de/',   
  'SettingsPermission' => FALSE
);

class TSServerPlugin extends Gdn_Plugin {

  public function PluginController_TSServer_Create(&$Sender) {
    $Sender->AddSideMenu('plugin/TSServer');
    $Sender->Form = new Gdn_Form();
    $Validation = new Gdn_Validation();
    $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
    $ConfigurationModel->SetField(array(
      'TSServer.IP' => 'localhost', 
      'TSServer.ID' => 1,
      'TSServer.QueryPort' => 10011,
      'TSServer.QueryUser' => 'serveradmin',
      'TSServer.QueryPass' => 'secret',
      'TSServer.DisplaySignedInOnly' => FALSE,
      'TSServer.ShowNicknameBox' => FALSE,
      'TSServer.ShowPasswordBox' => FALSE,
      'TSServer.Cache' => 5,
      'TSServer.CacheFile' => '/tmp/vanilla.tsserver.cache',
      'TSServer.DecodeUTF8' => FALSE,
      'TSServer.Timeout' => 2
    ));
    $Sender->Form->SetModel($ConfigurationModel);
    if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
      $Sender->Form->SetData($ConfigurationModel->Data);
    } else {
      $ConfigurationModel->Validation->ApplyRule('TSServer.ID', 'Integer');
      $ConfigurationModel->Validation->ApplyRule('TSServer.QueryPort', 'Integer');
      $ConfigurationModel->Validation->ApplyRule('TSServer.DisplaySignedInOnly', 'Boolean');
      $ConfigurationModel->Validation->ApplyRule('TSServer.ShowNicknameBox', 'Boolean');
      $ConfigurationModel->Validation->ApplyRule('TSServer.ShowPasswordBox', 'Boolean');
      $ConfigurationModel->Validation->ApplyRule('TSServer.Cache', 'Integer');
      $ConfigurationModel->Validation->ApplyRule('TSServer.DecodeUTF8', 'Boolean');
      $ConfigurationModel->Validation->ApplyRule('TSServer.Timeout', 'Integer');
      //$Data = $Sender->Form->FormValues();
      $Saved = $Sender->Form->Save();
      if ($Saved) {
	$Sender->StatusMessage = Gdn::Translate("Your settings have been saved.");
      }
    }
    $Sender->Render($this->GetView('dashboard.php'));
  }

  public function Base_Render_Before(&$Sender) {
    $Sender->AddJsFile('tsstatus.js', 'plugins/TSServer');
    $Sender->AddCssFile('tsserver.css', 'plugins/TSServer');
    include_once($this->GetView('displaymodule.php'));
    $DisplayModule = new displaymodule($Sender);
    $DisplayModule->GetData();
    $Sender->AddModule($DisplayModule);
  }

  public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
    $Menu = $Sender->EventArguments['SideMenu'];
    $Menu->AddLink('Add-ons', 'Teamspeak Server', 'plugin/TSServer', 'Garden.Themes.Manage');
  }

  public function Setup() {
    SaveToConfig("TSServer.IP", "localhost");
    SaveToConfig("TSServer.ID", 1);
    SaveToConfig("TSServer.QueryPort", 10011);
    SaveToConfig("TSServer.QueryUser", "serveradmin");
    SaveToConfig("TSServer.QueryPass", "secret");
    SaveToConfig("TSServer.DisplaySignedInOnly", FALSE);
    SaveToConfig("TSServer.ShowNicknameBox", FALSE);
    SaveToConfig("TSServer.ShowPasswordBox", FALSE);
    SaveToConfig("TSServer.Cache", 5);
    SaveToConfig("TSServer.CacheFile", "/tmp/vanilla.tsserver.cache");
    SaveToConfig("TSServer.DecodeUTF8", FALSE);
    SaveToConfig("TSServer.Timeout", 2);
  }
}
