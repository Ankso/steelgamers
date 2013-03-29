<?php if (!defined('APPLICATION')) exit();
/**
* # Vanilla jsConnect Auto SignIn #
* 
* ### About ###
* Forces sign in with the first available provider
* 
* ### Sponsor ###
* Special thanks to KyleIrving (www.kyle-irving.co.uk) for making this happen.
*/
/*
* Changlog;
* v.1.1b:Sun Apr 15 13:16:23 BST 2012
* - make mobile friendly
* - config options HideConnectButton & HideSignIn
* v.1.3b: Fri Mar 15 21:15:24 GMT 2013
* - jsconnectAuto.js form fix
* - guest module-less friendly
* v.1.4b: Mon Mar 18 11:11:16 GMT 2013
* - corrections on v.1.3b
*/

$PluginInfo['jsconnectAutoSignIn'] = array(
   'Name' => 'Vanilla jsConnect Auto SignIn',
   'Description' => 'Forces sign in with the first available provider',
   'Version' => '0.1.4b',
   'RequiredPlugins' => array('jsconnect' => '>=1.0.3b'),
   'Author' => 'Paul Thomas',
   'AuthorEmail' => 'dt01pqt_pt@yahoo.com ',
   'AuthorUrl' => 'http://www.vanillaforums.org/profile/x00'
);

class jsConnectAutoSignInPlugin extends Gdn_Plugin {
	public function Base_Render_Before($Sender, $Args) {
		if (!Gdn::Session()->UserID) {
			$Sender->AddCssFile('jsconnectAuto.css', 'plugins/jsconnectAutoSignIn');
			$Sender->AddJSFile('jsconnectAuto.js', 'plugins/jsconnectAutoSignIn');
			$Sender->AddDefinition('Connecting', T('Connecting','Connecting...'));
			$Sender->AddDefinition('ConnectingUser', T('ConnectingUser','Hi % just connecting you to forum...'));
			if (C('Plugins.jsconnectAutoSignIn.HideConnectButton') || IsMobile()) {
				$Sender->Head->AddString('<style type="text/css">.ConnectButton{display:none!important;}</style>');
			}
			$Providers = $this->GetProviders();
			if($Providers){
				$Sender->AddDefinition('JsConnectProviders', json_encode($Providers));
			}

		}
		
		if(C('Plugins.jsconnectAutoSignIn.HideSignIn')){
			$Sender->Head->AddString('<script type="text/javascript">' .
				'jQuery(document).ready(function($){' .
					'$(\'.ConnectButton,.SignInItem,a[href*="entry/signin"],a[href*="entry/signout"]\').hide();' .
				'});' .
				'</script>');
		}
	}
	//mobile and guest module-less friendly 
	public function GetProviders() {
		$Providers = JsConnectPlugin::GetProvider();
		$JsConnectProviders = array();
		foreach ($Providers as $Provider) {
			$Data = $Provider;
			$Target = Gdn::Request()->Get('Target');
			if (!$Target)
			$Target = '/'.ltrim(Gdn::Request()->Path());

			if (StringBeginsWith($Target, '/entry/signin'))
				$Target = '/';

			$ConnectQuery = array('client_id' => $Provider['AuthenticationKey'], 'Target' => $Target);
			$Data['Target'] = Url('entry/jsconnect', TRUE);
			if(strpos($url,'?') !== FALSE) {
			   $Data['Target'] .= '&'.http_build_query($ConnectQuery);
			} else {
			   $Data['Target'] .= '?'.http_build_query($ConnectQuery);
			}
			$Data['Target'] = urlencode($Data['Target']);
			$Data['Name'] = Gdn_Format::Text($Data['Name']);
			$Data['SignInUrl'] = FormatString(GetValue('SignInUrl', $Provider, ''), $Data);
			$JsConnectProviders[] = array('Name'=>$Data['Name'], 'SignInUrl'=>$Data['SignInUrl']);
		}
		return empty($JsConnectProviders) ? FALSE: $JsConnectProviders;
	}
	
	
	
	public function EntryController_JsConnectAuto_Create($Sender, $Args) {
		$client_id = $Sender->SetData('client_id', $Sender->Request->Get('client_id', 0));
		$Provider = JsConnectPlugin::GetProvider($client_id);

		if (empty($Provider))
			throw NotFoundException('Provider');

		$Get = ArrayTranslate($Sender->Request->Get(), array('client_id', 'display'));

		$Sender->SetData('JsAuthenticateUrl', JsConnectPlugin::ConnectUrl($Provider, TRUE));
		$Sender->Render('JsConnect', '', 'plugins/jsconnect');
	}
}
