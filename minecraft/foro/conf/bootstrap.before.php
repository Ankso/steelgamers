<?php if (!defined('APPLICATION')) exit();
     function SignOutUrl($Target = '') {
        $SignOutUrl = C('Garden.Authenticator.SignOutUrl','/signout/{Session_TransientKey}?Target=%2$s');
        $SignOutUrl = FormatString($SignOutUrl,array('Session_TransientKey'=>Gdn::Session()->TransientKey()));
        $SignOutUrl = sprintf($SignOutUrl,($Target ? '&Target='.urlencode($Target) : ''));
        return $SignOutUrl;
     }