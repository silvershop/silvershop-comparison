<% if $Me.Feature.ValueType = 'Boolean' || $Me.Feature.ValueType = 'Number' %>
	$TypedValue.Nice $Feature.Unit
<% else %>
	$TypedValue
<% end_if %>