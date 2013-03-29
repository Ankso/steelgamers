<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">
<head>
  {asset name='Head'}
</head>
<body id="{$BodyID}" class="{$BodyClass}">
  <div id="Frame">
    <div class="Head">
      <div class="Banner">
        <h1 class="Logo">{logo}</h1>
        {if $User.SignedIn}
        <div class="ProfileBox">
          <span class="Photo">
            {photo_link}
          </span>
		    {profile_link}
		    {inbox_link}
		    {signinout_link}
        </div>
        {/if}
      </div>
	   <div class="Menu">
		  <ul>
		    {dashboard_link}
		    {discussions_link}
		    {activity_link}
		    {custom_menu}
		  </ul>
		  <div class="SearchBox">{searchbox}</div>
	   </div>
	 </div>
	 <div id="Body">
		<div class="Wrapper">
		  <div id="Panel">
			 {asset name="Panel"}
		  </div>		  
		  <div id="Content">
			 {asset name="Content"}
		  </div>
		</div>
	 </div>
	 <div id="Foot">
		<div><a href="{vanillaurl}"><span>Powered by Vanilla</span></a></div>
		{asset name="Foot"}
	 </div>
  </div>
</body>
</html>