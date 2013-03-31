<?php
if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<h1><?php echo T("TSServer"); ?></h1>
<div class="Info"><?php echo T('Please leave here the required information!'); ?></div>
  <table class="AltRows">
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.IP'); ?></th>
      <td class="Alt"><?php echo T("The IP / Domain Name of the Teamspeak Server"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.ID'); ?></th>
      <td class="Alt"><?php echo T("The Teamspeak Server ID on which the server runs on"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.QueryPort'); ?></th>
      <td class="Alt"><?php echo T("The query port on which the Teamspeak Server runs on (default: 10011)"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.QueryUser'); ?></th>
      <td class="Alt"><?php echo T("The login name for the server Administrator (default: serveradmin)"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.QueryPass'); ?></th>
      <td class="Alt"><?php echo T("The login password for the Administrator"); ?></td>
    </tr>
  </table>
<div class="Info"><?php echo T('Extra information for customizing your TSViewer!'); ?></div>
  <table class="AltRows">
    <tr>
      <th><?php echo $this->Form->CheckBox('TSServer.DisplaySignedInOnly'); ?></th>
      <td class="Alt"><?php echo T("Display the viewer only if the user is logged in"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->CheckBox('TSServer.ShowNicknameBox'); ?></th>
      <td class="Alt"><?php echo T("Enable/Disable showing user login box on your TSViewer"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->CheckBox('TSServer.ShowPasswordBox'); ?></th>
      <td class="Alt"><?php echo T("Enable/Disable showing user password box on your TSViewer"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.Cache'); ?></th>
      <td class="Alt"><?php echo T("Activate caching system to prevent bans from the server in seconds"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.CacheFile'); ?></th>
      <td class="Alt"><?php echo T("This is the file were the cache data will be stored"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->CheckBox('TSServer.DecodeUTF8'); ?></th>
      <td class="Alt"><?php echo T("Enable/Disable UTF8 decoding"); ?></td>
    </tr>
    <tr>
      <th><?php echo $this->Form->TextBox('TSServer.Timeout'); ?></th>
      <td class="Alt"><?php echo T("Set the timeout value for your TSViewer (default: 2)"); ?></td>
    </tr>
  </table>
</div>
<?php
echo '<br>'.$this->Form->Close('Save');
