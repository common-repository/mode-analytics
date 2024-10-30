(function(wp, $){
	$(document).ready(function(){
		var addReportButton = $('#mode-analytics-add-report'),
			reportsTable = $('#mode-analytics-reports'),
			reportsTableBody = reportsTable.find('tbody.mode-analytics-reports-list');

		addReportButton.removeAttr('disabled');
		addReportButton.on('click', function(e){
			e.preventDefault();
			var template = wp.template( $(this).data('template') );
			var templateString = template();
			var highestIndex = 0;
			reportsTableBody.find('tr').each(function(){
				var index = $(this).data('index');
				if ( index > highestIndex ) {
					highestIndex = index;
				}
			});
			highestIndex++;
			templateString = templateString.replace(/%index%/g, highestIndex);
			reportsTableBody.append( templateString );
		});
		reportsTableBody.on('click', '.mode-analytics-remove-button', function(e){
			e.preventDefault();
			var row = $(this).closest('tr'),
				index = row.data('index');
			row.parent().children('[data-index=' + index + ']').remove();
		});
		reportsTableBody.on('click', '.mode-analytics-add-parameter', function(e){
			e.preventDefault();
			var parentRow = $(this).closest('tr'),
				parentIndex = parentRow.data('index'),
				parametersTBody = parentRow.find('.mode-analytics-report-parameters tbody');
			var template = wp.template( $(this).data('template') );
			var templateString = template();
			templateString = templateString.replace(/%parent_index%/g, parentIndex);
			var highestIndex = 0;
			parametersTBody.find('tr').each(function(){
				var index = $(this).data('index');
				if ( index > highestIndex ) {
					highestIndex = index;
				}
			});
			highestIndex++;
			templateString = templateString.replace(/%index%/g, highestIndex);
			parametersTBody.append( templateString );
		});
		reportsTableBody.on('change','.mode-analytics-data-type',function(e) {
			var defaultValue = $(this).closest('tr').find('.mode-analytics-default-value');
			defaultValue.attr('type',$(this).val());
		});
		reportsTableBody.on('change', '.mode-analytics-embed-type',function(e){
			$(this).closest('.mode-analytics-report-row').find('.mode-analytics-report-buttons').attr('data-embed-type', $(this).val());
		});
	})
}(window.wp, jQuery))
