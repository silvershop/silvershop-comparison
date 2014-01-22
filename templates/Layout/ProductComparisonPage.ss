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
			    	<td>
			    		$Value $Unit
			    	</td>
			    <% end_loop %>
		    </tr>
		<% end_loop %>
	</tbody>
	<tfoot>
		<tr><td></td><% loop Comp %><td><a href="$AddLink">Add to cart</a></td><% end_loop %></tr>
	</tfoot>
</table>