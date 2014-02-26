<div id="product_comparsion">
	<% if Comp %>
		<div id="product_comparsion__table">
			<table>
				<thead>
					<tr><td></td><% loop Comp %><td><a href="$CompareRemoveLink">remove</a></td><% end_loop %></tr>
					<tr><td></td><% loop Comp %><td>$Image</td><% end_loop %></tr>
					<tr><td></td><% loop Comp %><td>$Title</td><% end_loop %></tr>
					<tr><td></td><% loop Comp %><td>$Price</td><% end_loop %></tr>
					<tr><td></td><% loop Comp %><td><a href="$AddLink">Add to cart</a></td><% end_loop %></tr>
				</thead>
				<tbody>
					<% loop Features %>
					    <tr>
					    	<th>$Title</th>
					    	<% loop Up.ValuesForFeature($ID) %>
						    	<td><% include TypedValue %></td>
						    <% end_loop %>
					    </tr>
					<% end_loop %>
				</tbody>
				<tfoot>
					<tr><td></td><% loop Comp %><td><a href="$AddLink">Add to cart</a></td><% end_loop %></tr>
				</tfoot>
			</table>
		</div>
	<% else %>
		<div class="product_comparsion__no_products">
			<h2>Looks like you haven&#8299;t got any products to compare..</h2>
			<p>To get started first click the <strong>Compare</strong> button on product in the store.</p>
		</div>
	<% end_if %>
</div>