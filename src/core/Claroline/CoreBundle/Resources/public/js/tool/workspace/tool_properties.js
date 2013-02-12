(function(){
    var wsId = $('#tool-table').attr('data-workspace-id');
    var roleId = $('#tool-table').attr('data-role-id');

    $('.chk-tool-visible').on('change', function(e){
        var toolId = $(e.target.parentElement.parentElement).attr('data-tool-id');
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        var rows = $("#tool-table tr");
        var isCurrentRowChecked = rows.eq(rowIndex)[0].children[1].children[0].checked;
        var route = ''
        if (isCurrentRowChecked) {
            route = Routing.generate(
                'claro_tool_workspace_add',
                { 'toolId' : toolId, 'position': rowIndex, 'workspaceId': wsId, 'roleId': roleId }
            );
        } else {
            route = Routing.generate(
                'claro_tool_workspace_remove',
                { 'toolId' : toolId, 'workspaceId': wsId, 'roleId': roleId }
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
                    'claro_tool_workspace_move',
                    { 'toolId': toolId, 'position': index - 1, 'workspaceId': wsId, 'roleId': roleId }
                );
            } else {
                var toolId = $(rows.eq(index - 1)[0]).attr('data-tool-id');
                var route = Routing.generate(
                    'claro_tool_workspace',
                    { 'toolId': toolId, 'position': index, 'workspaceId': wsId, 'roleId': roleId }
                );
            }
            Claroline.Utilities.ajax({url:route, type:'POST'});

        } else {
            alert('you cannot move this row up');
        }
	}
})()

