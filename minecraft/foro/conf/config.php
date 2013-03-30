<?php if (!defined('APPLICATION')) exit();

// Conversations
$Configuration['Conversations']['Version'] = '2.0.18.4';

// Database
$Configuration['Database']['Name'] = 'forum';
$Configuration['Database']['Host'] = 'localhost';
$Configuration['Database']['User'] = 'root';
$Configuration['Database']['Password'] = 'vivamii7.com';

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledLocales
$Configuration['EnabledLocales']['spanish'] = 'es-ES';

// EnabledPlugins
$Configuration['EnabledPlugins']['HtmLawed'] = 'HtmLawed';
$Configuration['EnabledPlugins']['AllViewed'] = TRUE;
$Configuration['EnabledPlugins']['Flagging'] = TRUE;
$Configuration['EnabledPlugins']['Gravatar'] = TRUE;
$Configuration['EnabledPlugins']['VanillaInThisDiscussion'] = TRUE;
$Configuration['EnabledPlugins']['SplitMerge'] = TRUE;
$Configuration['EnabledPlugins']['Tagging'] = TRUE;
$Configuration['EnabledPlugins']['VanillaStats'] = TRUE;
$Configuration['EnabledPlugins']['WhosOnline'] = TRUE;
$Configuration['EnabledPlugins']['cleditor'] = TRUE;
$Configuration['EnabledPlugins']['FileUpload'] = TRUE;
$Configuration['EnabledPlugins']['jsconnect'] = TRUE;
$Configuration['EnabledPlugins']['CategoryAccordion'] = TRUE;

// Garden
$Configuration['Garden']['Title'] = 'Gaming Community';
$Configuration['Garden']['Cookie']['Salt'] = 'TR8AEN4E4F';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = FALSE;
$Configuration['Garden']['Registration']['Method'] = 'Connect';
$Configuration['Garden']['Registration']['CaptchaPrivateKey'] = '';
$Configuration['Garden']['Registration']['CaptchaPublicKey'] = '';
$Configuration['Garden']['Registration']['InviteExpiration'] = '-1 week';
$Configuration['Garden']['Registration']['ConfirmEmailRole'] = '3';
$Configuration['Garden']['Registration']['InviteRoles'] = 'a:8:{i:3;s:1:"0";i:4;s:1:"0";i:8;s:1:"0";i:16;s:1:"0";i:32;s:1:"0";i:33;s:1:"0";i:34;s:1:"0";i:35;s:1:"0";}';
$Configuration['Garden']['Email']['SupportName'] = 'Gaming Community';
$Configuration['Garden']['Version'] = '2.0.18.4';
$Configuration['Garden']['RewriteUrls'] = FALSE;
$Configuration['Garden']['CanProcessImages'] = TRUE;
$Configuration['Garden']['Installed'] = TRUE;
$Configuration['Garden']['Messages']['Cache'] = 'a:0:{}';
$Configuration['Garden']['Theme'] = 'community';
$Configuration['Garden']['Locale'] = 'es-ES';
$Configuration['Garden']['InstallationID'] = '8665-34133859-F12E33E9';
$Configuration['Garden']['InstallationSecret'] = '22ba55eaebb40c14e50e6c800a17f0b9ee345a73';
$Configuration['Garden']['EditContentTimeout'] = '-1';
$Configuration['Garden']['Authenticator']['SignOutUrl'] = 'http://localhost/logout.php?redirect=forum';

// Plugins
$Configuration['Plugins']['GettingStarted']['Dashboard'] = '1';
$Configuration['Plugins']['GettingStarted']['Categories'] = '1';
$Configuration['Plugins']['GettingStarted']['Discussion'] = '1';
$Configuration['Plugins']['GettingStarted']['Plugins'] = '1';
$Configuration['Plugins']['GettingStarted']['Registration'] = '1';
$Configuration['Plugins']['GettingStarted']['Profile'] = '1';
$Configuration['Plugins']['FileUpload']['Enabled'] = TRUE;
$Configuration['Plugins']['Tagging']['Enabled'] = FALSE;
$Configuration['Plugins']['Flagging']['Enabled'] = TRUE;

// Routes
$Configuration['Routes']['DefaultController'] = 'a:2:{i:0;s:22:"categories/discussions";i:1;s:8:"Internal";}';

// Vanilla
$Configuration['Vanilla']['Version'] = '2.0.18.4';
$Configuration['Vanilla']['Categories']['MaxDisplayDepth'] = '3';
$Configuration['Vanilla']['Categories']['DoHeadings'] = '1';
$Configuration['Vanilla']['Categories']['HideModule'] = FALSE;
$Configuration['Vanilla']['AdminCheckboxes']['Use'] = TRUE;
$Configuration['Vanilla']['Discussion']['SpamCount'] = '3';
$Configuration['Vanilla']['Discussion']['SpamTime'] = '60';
$Configuration['Vanilla']['Discussion']['SpamLock'] = '600';
$Configuration['Vanilla']['Comment']['SpamCount'] = '5';
$Configuration['Vanilla']['Comment']['SpamTime'] = '60';
$Configuration['Vanilla']['Comment']['SpamLock'] = '600';
$Configuration['Vanilla']['Comment']['MaxLength'] = '8000';
$Configuration['Vanilla']['Discussions']['PerPage'] = '30';
$Configuration['Vanilla']['Comments']['AutoRefresh'] = '60';
$Configuration['Vanilla']['Comments']['PerPage'] = '50';
$Configuration['Vanilla']['Archive']['Date'] = '';
$Configuration['Vanilla']['Archive']['Exclude'] = FALSE;

// Last edited by Ankso (127.0.0.1)2013-03-27 21:43:35