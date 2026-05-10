<a class="silvershop-product-specs__compare-link" href="$CompareLink">add to comparison</a>

<% if $Features %>
    <h3 class="silvershop-product-specs__heading">Specifications</h3>
    <table class="silvershop-product-specs">
		<%-- Enable grouping by including <%  % --%>
		<% if $Grouping %>
			<% loop $GroupedFeatures %>
                <tr class="silvershop-product-specs__row silvershop-product-specs__row--group">
                    <th class="silvershop-product-specs__cell silvershop-product-specs__cell--group" colspan="2">
						<% if $Group %>
							$Group.Title
						<% else %>
                            Ungrouped
						<% end_if %>
                    </th>
                </tr>
				<% loop $Children %>
                    <tr class="silvershop-product-specs__row silvershop-product-specs__row--feature">
                        <th class="silvershop-product-specs__cell silvershop-product-specs__cell--feature">$Title</th>
                        <td class="silvershop-product-specs__cell silvershop-product-specs__cell--value"><% include SilverShop\Comparison\Includes\TypedValue %></td>
                    </tr>
				<% end_loop %>
			<% end_loop %>
		<% else %>
			<% loop $Features %>
                <tr class="silvershop-product-specs__row silvershop-product-specs__row--feature">
                    <th class="silvershop-product-specs__cell silvershop-product-specs__cell--feature">$Title</th>
                    <td class="silvershop-product-specs__cell silvershop-product-specs__cell--value"><% include SilverShop\Comparison\Includes\TypedValue %></td>
                </tr>
			<% end_loop %>
		<% end_if %>
    </table>
<% end_if %>
