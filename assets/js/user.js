(function(wp, $){
	$(document).ready(function(){
		var addTokenButton = $('#mode-analytics-add-token'),
			tokensTable = $('#mode-analytics-tokens'),
			tokensTableBody = tokensTable.find('tbody');
		addTokenButton.removeAttr('disabled');
		addTokenButton.on('click', function(e){
			e.preventDefault();
			var template = wp.template( $(this).data('template') );
			var templateString = template();
			var highestIndex = 0;
			tokensTableBody.find('tr').each(function(){
				var index = $(this).data('index');
				if ( index > highestIndex ) {
					highestIndex = index;
				}
			});
			highestIndex++;
			templateString = templateString.replace(/%index%/g, highestIndex);
			tokensTableBody.append( templateString );
		});
		tokensTableBody.on('click', '.mode-analytics-remove-button', function(e){
			e.preventDefault();
			var row = $(this).closest('tr'),
				index = row.data('index');
			row.parent().children('[data-index=' + index + ']').remove();
		});
	});
}(window.wp, jQuery))
