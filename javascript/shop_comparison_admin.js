(function($) {
	$("body").on('change', '.on_feature_select_fetch_value_field', function(e) {
		var value = $(this).parents("td").siblings(".col-Value"),
			data = {
				ID: $(this).val(),
				SecurityID: $("input[name=SecurityID]").val(),
				Name: value.find(':input').first().attr('name')
			};

		value.html('');

		$.get('ProductFeatureValueFieldController', data, function(data) {
			value.html(data);
		});
	});
})(jQuery);