/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    $('#edit-tools-btn').on('click', function (e) {
        e.preventDefault();
        var formData = new FormData(document.getElementById('desktop-tool-form'));
        var url = $('#desktop-tool-form').attr('action');

        $('#tool-table tr').each(function (index) {
            if ($(this).attr('data-tool-id')) {
                formData.append('tool-' + $(this).attr('data-tool-id'), index);
            }
        });

        var redirect = $(this).attr('href');

        $.ajax({
            url: url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                window.location = redirect;
            }
        });
    });
    
    $('#tools-table-body').sortable({
        items: 'tr',
        cursor: 'move'
    });
    
    $('#tools-table-body').on('sortupdate', function (event, ui) {
        
        if (this === ui.item.parents('#tools-table-body')[0]) {
            var orderedToolId = $(ui.item).data('ordered-tool-id');
            var nextOrderedToolId = $(ui.item).next().data('ordered-tool-id');
            var previousOrderedToolId = $(ui.item).prev().data('ordered-tool-id');
            var execute = false;
            var otherOrderedToolId;
            var mode;
            
            if (nextOrderedToolId !== undefined) {
                otherOrderedToolId = nextOrderedToolId;
                mode = 'next';
                execute = true;
            } else if (previousOrderedToolId !== undefined) {
                otherOrderedToolId = previousOrderedToolId;
                mode = 'previous';
                execute = true;
            }
            
            if (execute) {
                $.ajax({
                    url: Routing.generate(
                        'claro_desktop_update_ordered_tool_order',
                        {
                            'orderedTool': orderedToolId,
                            'otherOrderedTool': otherOrderedToolId,
                            'mode': mode
                        }
                    ),
                    type: 'POST'
                });
            }
        }
    });
})();

