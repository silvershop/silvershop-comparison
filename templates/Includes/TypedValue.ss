<% if $Feature %>
	<% if $Feature.ValueType = 'Boolean' || $Feature.ValueType = 'Number' %>
		$TypedValue.Nice $Feature.Unit
	<% else %>
		$TypedValue
	<% end_if %>
<% else %>
<% end_if %>
