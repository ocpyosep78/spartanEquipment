		<table class="GridPanel SortableGrid" cellspacing="0" cellpadding="0" border="0" id="IndexGrid" style="width:100%;">
				<tr align="right">
					<td colspan="10" style="padding:6px 0px 6px 0px" class="PagingNav">
						{{ Nav|raw }}
						<br />
					</td>
				</tr>
			<tr class="Heading3">
				<td align="center" style="width:18px"><input type="checkbox" onclick="ToggleDeleteBoxes(this.checked)"></td>
				<td>&nbsp;</td>
				<td>
					{% lang 'CouponName' %} &nbsp;
					{{ SortLinksName|raw }}
				</td>
				<td nowrap>
					{% lang 'CouponCode' %} &nbsp;
					{{ SortLinksCoupon|raw }}
				</td>
				<td nowrap>
					{% lang 'Discount' %} &nbsp;
					{{ SortLinksDiscount|raw }}
				</td>
				<td nowrap>
					<div style="display:none" id="invDiv" name="invDiv"></div>
					{% lang 'ExpiryDate' %}
					{{ SortLinksExpiry|raw }}
				</td>
				<td nowrap>
					{% lang 'NumUses' %}
					{{ SortLinksNumUses|raw }}
				</td>
				<td style="width:80px">
					{% lang 'Enabled' %} &nbsp;
					{{ SortLinksEnabled|raw }}
				</td>
				<td style="width:130px">
					{% lang 'Action' %}
				</td>
			</tr>
			{{ CouponGrid|raw }}
			<tr align="right">
				<td colspan="10" style="padding:6px 0px 6px 0px" class="PagingNav">
					{{ Nav|raw }}
				</td>
			</tr>
		</table>