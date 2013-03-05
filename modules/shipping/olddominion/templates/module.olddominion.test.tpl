<form action="index.php?ToDo=testShippingProviderQuote" method="post" onsubmit="return ValidateForm(CheckQuoteForm)">

<input type="hidden" name="methodId" value="{{ MethodId|raw }}" />

<fieldset style="margin:10px">

<legend>{% lang 'OldDominionShippingQuote' %}</legend>

<table width="100%" style="background-color:#fff" class="Panel">

	<tr>

		<td style="padding-left:15px">&nbsp;

			

		</td>

		<td>

			<img style="margin-top:5px" src="../modules/shipping/olddominion/images/{{ Image|raw }}" />

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			<span class="Required">*</span> {% lang 'OldDominionDestinationCountry' %}:

		</td>

		<td>

			<select name="delivery_country" id="delivery_country" class="Field250">

				<option selected="selected" value="226">United States</option>

			</select>

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			<span class="Required">*</span> {% lang 'OldDominionDestinationType' %}:

		</td>

		<td>

			<select name="delivery_destination" id="delivery_destination" class="Field250">

				<option value="RES">{% lang 'OldDominionResidential' %}</option>

				<option value="COM">{% lang 'OldDominionCommercial' %}</option>

			</select>

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			<span class="Required">*</span> {% lang 'OldDominionDestinationZip' %}:

		</td>

		<td>

			<input name="delivery_zip" id="delivery_zip" class="Field50">

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">

			<span class="Required">*</span> {% lang 'OldDominionPackageWeight' %}:

		</td>

		<td>

			<input name="delivery_weight" id="delivery_weight" class="Field50">{{ WeightUnit|raw }}

		</td>

	</tr>

	<tr>

		<td style="padding-left:15px">&nbsp;

			

		</td>

		<td class="PanelBottom">

			<input type="submit" class="FormButton" style="width:120px" value="{% lang 'GetShippingQuote' %}">

		</td>

	</tr>

</table>

</legend>



<script type="text/javascript">



	function CheckQuoteForm() {

		var delivery_zip = document.getElementById("delivery_zip");

		var delivery_weight = document.getElementById("delivery_weight");



		if(delivery_zip.value == "") {

			alert("{% lang 'OldDominionEnterDestinationZip' %}");

			delivery_zip.focus();

			return false;

		}



		if(isNaN(delivery_weight.value) || delivery_weight.value == "") {

			alert("{% lang 'OldDominionEnterValidWeight' %}");

			delivery_weight.focus();

			delivery_weight.select();

			return false;

		}



		return true;

	}



</script>



