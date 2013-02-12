(function(){
    var wsId = $('#tool-table').attr('data-workspace-id');

    $('.chk-tool-visible').on('change', function(e){
        var toolId = $(e.target.parentElement).attr('data-tool-id');
        var roleId = $(e.target.parentElement).attr('data-role-id');
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        var isCurrentElementChecked = e.currentTarget.checked;
        var route = ''
        if (isCurrentElementChecked) {
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
        var rows = $("#tool-table tr");
        var toolId = $(rows.eq(rowIndex)[0].children[1]).attr('data-tool-id');
        MoveRowUp(rowIndex, toolId);
    })

	function MoveRowUp(index, toolId) {
        var rows = $("#tool-table tr");
        if (index !== 1) {
            rows.eq(index).insertBefore(rows.eq(index - 1));
                var route = Routing.generate(
                    'claro_tool_workspace_move',
                    { 'toolId': toolId, 'position': index - 1, 'workspaceId': wsId }
                );
            Claroline.Utilities.ajax({url:route, type:'POST'});

        } else {
            alert('you cannot move this row up');
        }
	}
})()

