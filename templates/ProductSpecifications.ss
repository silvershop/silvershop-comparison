<a href="$CompareLink">add to comparison</a>

<% if Features %>
	<h3>Specifications</h3>
	<table>
		<% if Grouping %>
			<% loop GroupedFeatures %>
					<tr>
						<th colspan="2">
							<% if Group %>
								$Group.Title
							<% else %>
								Ungrouped
							<% end_if %>
						</th>
					</tr>
			    <% loop Children %>
					<tr>
						<th>$Title</th>
						<td><% include TypedValue %></td>
					</tr>
				<% end_loop %>
			<% end_loop %>
		<% else %>
			<% loop Features %>
				<tr>
					<th>$Title</th>
					<td><% include TypedValue %></td>
				</tr>
			<% end_loop %>
		<% end_if %>
	</table>
<% end_if %>