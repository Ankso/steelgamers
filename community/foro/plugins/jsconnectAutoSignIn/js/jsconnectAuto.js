jQuery(document).ready(function($) {
   
$.fn.jsconnectAuto = function(options) {
	if (this.length == 0)
		return;
		
	function AutoAuthenticate(Url){
		$.ajax({
			url: Url,
			dataType: 'json',
			success: function(data, textStatus) {
				$.ajax({
					url: data.JsAuthenticateUrl,
					dataType: 'json',
					success: function(data) {
						
						var action = '';
						if (data['error']) {
							action = gdn.url('/entry/jsconnect/error');
						} else if (!data['name']) {
							//data = {'error': 'unauthorized', 'message': 'You are not signed in.' };
							//action = gdn.url('/entry/jsconnect/guest');
							return;
							
						} else {
							for(var key in data) {
								if (data[key] == null)
									data[key] = '';
							}
							action = gdn.url('/entry/connect/jsconnect?client_id='+data['client_id']+'&display=1');
						}
						
						
						var smokescreen = $(
							'<div id="smokescreen-panel" class="Popup">'+
								'<div class="Border">'+
									'<div id="smokescreen-panel-box" class="Body">'+
									'</div>'+
								'</div>'+
							'</div>'+
							'<div id="smokescreen"> </div>'
						);
						
						$(document.body).append(smokescreen);
						
						$('#smokescreen-panel-box').append('<h1 style="text-align: center;">'+gdn.definition('Connecting')+'</h1>');
						$('#smokescreen-panel-box').append(('<p class="Message">'+gdn.definition('ConnectingUser')+'</p>').replace(/%/,$(this).children('.Username').text()));
						$('#smokescreen-panel-box').append('<div class="Progress"></div><br />');
						
						$("#smokescreen, #smokescreen-panel").show();  
						setTimeout(function(){$("#smokescreen, #smokescreen-panel").hide();},1000*60);
											
						var jsConnectForm = $('<form>').attr({
												'id':'jsConnectAuto',
												'method':'post',
												//'style':'display:none;',
												'action':action
											});
											
						jsConnectForm.append($('<input type="hidden" name="Form/JsConnect" />').val($.param(data)));												
						jsConnectForm.append($('<input type="hidden" name="Form/Target" />').val(document.location.toString()));
						jsConnectForm.append($('<input type="hidden" name="Form/TransientKey" />').val(gdn.definition('TransientKey')));
						jsConnectForm.find('input').each(function(){
							if($(this).attr('name').match(/^Form\//)!=-1){
								jsConnectForm.append($('<input type="hidden" name="'+$(this).attr('name').replace(/^Form\//,'')+'" />').val($(this).val()));
							}
						});
							
					
						$(document.body).append(jsConnectForm);
						$('#jsConnectAuto').submit();
					},
					error: function(data, x, y) {
						//$('form').attr('action', gdn.url('/entry/jsconnect/error'));
					}
			    });
			}
		});
	}
   
	var url = $(this).attr('href');
	var re = new RegExp("client_?id=([^&]+)(&Target=([^&]+))?", "g");
	var matches = re.exec(url);
	var client_id = false, target = '/';
	if (matches) {
		if (matches[1])
			client_id = matches[1];
		if (matches[3])
			target = matches[3];
	}
	
	var connectUrl = gdn.url('/entry.json/jsconnectauto?client_id='+client_id+'&Target='+target);

	AutoAuthenticate(connectUrl);
   
};
/*find comments*/
var comments = $("body *:not(iframe)").contents().filter(function(){ return this.nodeType == 8;});

/*get commented jsConnect buttons*/
comments.each(function(){
	var m = this.nodeValue.match(/^\|jsConnectAuto|([\s\S]*)/gm);
	if(m && m.length>1){
		$(document.body).append(m[1]);
	}
});

var providers = $.parseJSON(gdn.definition('JsConnectProviders'));

if(providers.length){
	provider = providers[0];
	$(document.body).prepend('<div style="display:none;"><a href="'+provider['SignInUrl']+'" class="ConnectLinkAuto"><span class="Username">'+provider['Name']+'</span></a></div>');
}

$('.ConnectLinkAuto[href*="client_id"]:first').jsconnectAuto();



});
