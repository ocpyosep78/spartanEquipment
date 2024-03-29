<fieldset style="margin:10px">

<legend>{% lang 'OldDominionShippingQuote' %}</legend>

<table width="100%" style="background-color:#fff" class="Panel">

	<tr>

		<td style="width:120px; padding-left:15px">&nbsp;

			

		</td>

		<td>

			<img style="margin-top:5px" src="../modules/shipping/OldDominion/images/{{ Image|raw }}" />

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			{% lang 'QuoteStatus' %}:

		</td>

		<td style="color:{{ Color|raw }}">

			<strong>{{ Status|raw }}</strong>

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			{{ Label|raw }}:

		</td>

		<td style="color:{{ Color|raw }}">

			{{ Message|raw }}<br />

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">&nbsp;

			

		</td>

		<td class="PanelBottom" style="color:{{ Color|raw }}">

			<input type="button" class="FormButton" style="width:120px" value="{% lang 'NewShippingQuote' %}" onclick="history.go(-1)" />

			<input type="button" class="FormButton" style="width:90px" value="{% lang 'CloseWindow' %}" onclick="window.close()" />

		</td>

	</tr>

</table>

</fieldset>

