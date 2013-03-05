<label style="display: block;"><input type="radio" checked="checked" name="ol_register" onclick="ToggleOlark(this.value);" value="1" /> {% lang 'OlarkDontHaveCreate' %}</label>
<div id="ol_register_show">
	
	<img src="images/nodejoin.gif" alt="" /> <input type="button" value="{% lang 'OlarkCreateAccount' %}" onclick="window.open('http://www.olark.com/?r=kvo8wc4k')" class="FormButton" style="width:170px" />
</div>

<label style="display: block;"><input type="radio" name="ol_register" onclick="ToggleOlark(this.value);" value="0" /> {% lang 'OlarkHaveAccount' %}</label>
<div style="display: none;" id="ol_existing_show">
	<img src="images/nodejoin.gif" alt="" style="float: left;" />
	<div style="float: left; padding-top: 5px; padding-left: 10px;">
		{% lang 'OlarkExistingUserInstructions' %}
	</div>
</div>
<script type="text/javascript">
	function ToggleOlark(value)
	{
		if(value == 1) {
			$('#ol_register_show').show();
			$('#ol_existing_show').hide();
			$('.properties_livechat_olark:first').show();
			$('.properties_livechat_olark:gt(1)').hide();
			$('#livechat_olark_chatcode').val(null);
		}
		else {
			$('#ol_register_show').hide();
			$('#ol_existing_show').show();
			$('.properties_livechat_olark').show();
		}
	}
	
	function CreateOlarkAccount()
	{
		tb_show('', "index.php?ToDo=liveChatSettingsCallback&module=livechat_olark&func=ShowOlarkRegistration&height=320&width=460&modal=true&TB_iframe=true");
	}

	function IntegrateOlark()
	{
		$('#frmLiveChatSettings').submit();
	}
	
	$(document).ready(function(){
		$('.properties_livechat_olark:first').show();
		if($('#livechat_olark_chatcode').html()){
			$('form input:radio[name=ol_register]:last').click()
		} else {
			$('form input:radio[name=ol_register]:first').click()
		}
	});
</script>
<textarea name="ol_chatcode" id="ol_chatcode" style="display: none">
</textarea>
