(function () {
    'use strict';

    var stackedRequests = 0;
    $.ajaxSetup({
        beforeSend: function () {
            stackedRequests++;
            $('.please-wait').show();
        },
        complete: function () {
            stackedRequests--;
            if (stackedRequests === 0) {
                $('.please-wait').hide();
            }
        }
    });

    var wsId = $('#tool-table').attr('data-workspace-id');

    $('.chk-tool-visible').on('change', function (e) {
        var toolId = $(e.target.parentElement).attr('data-tool-id');
        var roleId = $(e.target.parentElement).attr('data-role-id');
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        var isCurrentElementChecked = e.currentTarget.checked;
        var route = '';
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
        $.ajax({url: route, type: 'POST'});
    });

    $('.icon-circle-arrow-up').on('click', function (e) {
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        moveRowUp(rowIndex);
    });

    $('.icon-circle-arrow-down').on('click', function (e) {
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        moveRowDown(rowIndex);
    });

	function moveRowUp(index) {
        var toolId;
        var isRowChecked = false;
        var rows = $('#tool-table tr');
        $(rows.eq(index)[0].children).each(function (i, e) {
            if ($(e.children[0]).attr('checked') === 'checked') {
                isRowChecked = true;
            }
        });
        if (index !== 1) {
            rows.eq(index).insertBefore(rows.eq(index - 1));
            if (isRowChecked) {
                toolId = $(rows.eq(index)[0].children[1]).attr('data-tool-id');
                index = index - 1;
            } else {
                toolId = $(rows.eq(index - 1)[0].children[1]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_workspace_move',
                { 'toolId': toolId, 'position': index, 'workspaceId': wsId }
            );
            $.ajax({url: route, type: 'POST'});
        }
	}

    function moveRowDown(index) {
        var rows = $('#tool-table tr');
        rows.eq(index).insertAfter(rows.eq(index + 1));
        var size = $('#tool-table tr').length;
        var isRowChecked = false;
        var toolId;

        $(rows.eq(index)[0].children).each(function (i, e) {
            if ($(e.children[0]).attr('checked') === 'checked') {
                isRowChecked = true;
            }
        });
        rows.eq(index).insertAfter(rows.eq(index + 1));
        if (index !== size) {
            if (isRowChecked) {
                toolId = $(rows.eq(index)[0].children[1]).attr('data-tool-id');
                index = index + 1;
            } else {
                toolId = $(rows.eq(index + 1)[0].children[1]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_workspace_move',
                { 'toolId': toolId, 'position': index, 'workspaceId': wsId }
            );
            $.ajax({url: route, type: 'POST'});
        }
    }
})();

