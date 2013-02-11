(function(){
    $('.chk-tool-visible').on('change', function(e){
        var toolId = $(e.target.parentElement.parentElement).attr('data-tool-id');
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        var rows = $("#tool-table tr");
        var isCurrentRowChecked = rows.eq(rowIndex)[0].children[1].children[0].checked;
        var route = ''
        if (isCurrentRowChecked) {
            route = Routing.generate(
                'claro_tool_desktop_add',
                { 'toolId' : toolId, 'position': rowIndex }
            );
        } else {
            route = Routing.generate(
                'claro_tool_desktop_remove',
                { 'toolId' : toolId }
            );
        }

        Claroline.Utilities.ajax({url:route, type:'POST'});
    })

    $('.icon-circle-arrow-up').on('click', function(e){
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        MoveRowUp(rowIndex);
    })

	function MoveRowUp(index) {
        var rows = $("#tool-table tr");
        var isCurrentRowChecked = rows.eq(index)[0].children[1].children[0].checked;

        if (index !== 1) {

            rows.eq(index).insertBefore(rows.eq(index - 1));
            if (isCurrentRowChecked) {
                var toolId = $(rows.eq(index)[0]).attr('data-tool-id');
                var route = Routing.generate(
                    'claro_tool_desktop_move',
                    { 'toolId': toolId, 'position': index - 1 }
                );
            } else {
                var toolId = $(rows.eq(index - 1)[0]).attr('data-tool-id');
                var route = Routing.generate(
                    'claro_tool_desktop_move',
                    { 'toolId': toolId, 'position': index }
                );
            }
            Claroline.Utilities.ajax({url:route, type:'POST'});

        } else {
            alert('you cannot move this row up');
        }
	}
})()

