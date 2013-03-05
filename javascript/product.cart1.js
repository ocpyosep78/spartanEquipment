var Cart = {
	ToggleShippingEstimation: function()
	{
		$('.EstimatedShippingMethods').hide();
		$('.EstimateShipping').toggle();
		$('.EstimateShippingLink').toggle();
		$('.EstimateShipping select:eq(0)').focus();
	},

	EstimateShipping: function()
	{
		//if ($('#shippingZoneCountry').val() == 0) {
			//alert(lang.SelectCountry);
			//$('#shippingZoneCountry').focus();
			//return;
		//}

		if ($('#shippingZoneState').is(':visible') && $('#shippingZoneState').val() == 0) {
			alert('Please Select a state');
			$('#shippingZoneState').focus();
			return;
		}

		if ($.trim($('#shippingZoneZip').val()) == '') {
			alert('Please enter a zipcode');
			$('#shippingZoneZip').focus();
			return;
		}

		$('.EstimatedShippingMethods').hide();
		$('.EstimateShipping .EstimateShippingButtons span').hide();
		$('.EstimateShipping .EstimateShippingButtons input')
			.data('oldVal', $('.EstimateShipping .EstimateShippingButtons input').val())
			.val(lang.Calculating)
			.attr('disabled', true)
		;
		var productweight = $('.VariationProductWeight').html();
		productweight = productweight.replace(/[^![0-9\.]/g, '');
		$.ajax({
			url: '/ndx_remote.php',
			type: 'post',
			data: {
				//w: 'getShippingQuotes',
				countryId: $('#shippingZoneCountry').val(),
				quantity_select: $('#qty_').val(),
				quantity_text: $('#text_qty_').val(),
				stateId: $('#shippingZoneState').val(),
				weight: productweight,
				zipCode: $('#shippingZoneZip').val(),
				product_id: $('input[name="product_id"]').val()
			},
			success: function(data)
			{
				$('.EstimatedShippingMethods .ShippingMethodList').html(data);
				$('.EstimatedShippingMethods').show();
				$('.EstimateShipping .EstimateShippingButtons span').show();
				$('.EstimateShipping .EstimateShippingButtons input')
					.val($('..EstimateShipping .EstimateShippingButtons input').data('oldVal'))
					.attr('disabled', false)
				;
			}
		});
	},
	
	ToggleShippingEstimateCountry: function()
	{
		var countryId = $('#shippingZoneCountry').val();
		$.ajax({
			url: '/ndx_remote.php',
			type: 'post',
			data: 'w=countryStates&c='+countryId,
			success: function(data)
			{
				$('#shippingZoneState option:gt(0)').remove();
				var states = data.split('~');
				var numStates = 0;
				for(var i =0; i < states.length; ++i) {
					vals = states[i].split('|');
					if(!vals[0]) {
						continue;
					}
					$('#shippingZoneState').append('<option value="'+vals[1]+'">'+vals[0]+'</option>');
					++numStates;
				}

				if(numStates == 0) {
					$('#shippingZoneState').hide();
					$('#shippingZoneStateName').show();
					$('#shippingZoneStateRequired').hide();
				}
				else {
					$('#shippingZoneState').show();
					$('#shippingZoneStateName').hide();
					$('#shippingZoneStateRequired').show();
				}
				$('#shippingZoneState').val('0');
			}
		});
	},


};