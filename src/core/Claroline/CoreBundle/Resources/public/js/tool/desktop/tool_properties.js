(function(){
    $('.chk-tool-visible').on('change', function(e){
        var toolId = $(e.target.parentElement.parentElement).attr('data-tool-id');
        var route = Routing.generate(
            'claro_tool_invert_visibility',
            { 'toolId' : toolId }
        );
        Claroline.Utilities.ajax({url:route, type:'POST'});
    })

    $('.icon-circle-arrow-up').on('click', function(e){
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        MoveRowUp(rowIndex);
    })

	function MoveRowUp(index) {
        var rows = $("#tool-table tr");
        var isCurrentRowChecked = rows.eq(index)[0].children[1].children[0].checked;
        var isPrevRowChecked = rows.eq(index - 1)[0].children[1].children[0].checked;

        if (index !== 1 && isCurrentRowChecked && isPrevRowChecked) {
            var toolId = $(rows.eq(index)[0]).attr('data-tool-id');
            var route = Routing.generate(
                'claro_tool_move_up',
                { 'toolId': toolId }
            );
            Claroline.Utilities.ajax({url:route, type:'POST'});
            rows.eq(index).insertBefore(rows.eq(index - 1));
        } else {
            alert('you cannot move this row up');
        }
	}
})()

