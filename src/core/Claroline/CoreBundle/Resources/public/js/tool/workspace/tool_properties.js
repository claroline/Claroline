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
        MoveRowUp(rowIndex);
    })

    $('.icon-circle-arrow-down').on('click', function(e){
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        MoveRowDown(rowIndex);
    })

	function MoveRowUp(index) {
        var isRowChecked = false;
        var rows = $("#tool-table tr");
        $(rows.eq(index)[0].children).each(function(i, e) {
            if ($(e.children[0]).attr('checked') === 'checked') {
                isRowChecked = true;
            }
        });
        if (index !== 1) {
            rows.eq(index).insertBefore(rows.eq(index - 1));
            if (isRowChecked) {
                var toolId = $(rows.eq(index)[0].children[1]).attr('data-tool-id');
                index = index - 1;
            } else {
                var toolId = $(rows.eq(index - 1)[0].children[1]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_workspace_move',
                { 'toolId': toolId, 'position': index, 'workspaceId': wsId }
            );
            Claroline.Utilities.ajax({url:route, type:'POST'});
        }
	}

    function MoveRowDown(index) {
        var rows = $("#tool-table tr");
        rows.eq(index).insertAfter(rows.eq(index + 1));
        var size = $("#tool-table tr").length;
        var isRowChecked = false;
        $(rows.eq(index)[0].children).each(function(i, e) {
            if ($(e.children[0]).attr('checked') === 'checked') {
                isRowChecked = true;
            }
        });
        rows.eq(index).insertAfter(rows.eq(index + 1));
        if (index !== size) {
            if (isRowChecked) {
                var toolId = $(rows.eq(index)[0].children[1]).attr('data-tool-id');
                index = index + 1;
            } else {
                var toolId = $(rows.eq(index + 1)[0].children[1]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_workspace_move',
                { 'toolId': toolId, 'position': index, 'workspaceId': wsId }
            );
            Claroline.Utilities.ajax({url:route, type:'POST'});
        }
    }
})()

