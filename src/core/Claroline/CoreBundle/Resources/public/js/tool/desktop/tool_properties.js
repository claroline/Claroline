(function(){
    var stackedRequests = 0;
    $.ajaxSetup({
        beforeSend: function() {
            stackedRequests++;
            $('.please-wait').show();
        },
        complete: function() {
            stackedRequests--;
            if (stackedRequests === 0) {
                $('.please-wait').hide();
            }
        }
    });

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

    $('.icon-circle-arrow-down').on('click', function(e){
        var rowIndex = e.target.parentElement.parentElement.rowIndex;
        MoveRowDown(rowIndex);
    })

	function MoveRowUp(index) {
        var rows = $("#tool-table tr");
        if (index !== 1) {
            rows.eq(index).insertBefore(rows.eq(index - 1));
            var isCurrentRowChecked = rows.eq(index)[0].children[1].children[0].checked;
            if (isCurrentRowChecked) {
                var toolId = $(rows.eq(index)[0]).attr('data-tool-id');
                index = index - 1;
            } else {
                var toolId = $(rows.eq(index - 1)[0]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_desktop_move',
                { 'toolId': toolId, 'position': index }
            );
            Claroline.Utilities.ajax({url:route, type:'POST'});
        }
	}

    function MoveRowDown(index) {
        var rows = $("#tool-table tr");
        rows.eq(index).insertAfter(rows.eq(index + 1));
        var size = $("#tool-table tr").length;

        if (index !== size) {
            var isCurrentRowChecked = rows.eq(index)[0].children[1].children[0].checked;
            if (isCurrentRowChecked) {
                var toolId = $(rows.eq(index)[0]).attr('data-tool-id');
                index = index + 1;
            } else {
                var toolId = $(rows.eq(index + 1)[0]).attr('data-tool-id');
            }
            var route = Routing.generate(
                'claro_tool_desktop_move',
                { 'toolId': toolId, 'position': index }
            );
        }
        Claroline.Utilities.ajax({url:route, type:'POST'});
    }
})()

