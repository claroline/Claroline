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

    $('.show-tool-edit-form').on('click', function (e) {
        e.preventDefault();
        window.Claroline.Modal.displayForm(
            $(this).attr('href'),
            editName,
            function() {},
            'edit-tool-name'
        );
    });
    
    $('#tool-table-body').sortable({
        items: 'tr',
        cursor: 'move'
    });
    
    $('#tool-table-body').on('sortupdate', function (event, ui) {
        
        if (this === ui.item.parents('#tool-table-body')[0]) {
            var workspaceId = $('#tool-table').data('workspace-id');
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
                        'claro_workspace_update_ordered_tool_order',
                        {
                            'workspace': workspaceId,
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
    
    $('#tool-table-body').on('click', '.granted-btn', function () {
        var currentBtn = $(this);
        var orderedToolId = $(this).data('ordered-tool-id');
        var roleId = $(this).data('role-id');
        var action = $(this).data('decoder-name');
        var iconClass = $(this).data('icon-class');
        var inverseIconClass = $(this).data('inverse-icon-class');
        
        $.ajax({
            url: Routing.generate(
                'claro_workspace_inverse_ordered_tool_right',
                {
                    'orderedTool': orderedToolId,
                    'role': roleId,
                    'action': action
                }
            ),
            type: 'POST',
            success: function() {
                currentBtn.removeClass('granted-btn');
                currentBtn.removeClass('text-success');
                currentBtn.removeClass(iconClass);
                currentBtn.addClass('denied-btn');
                currentBtn.addClass('text-danger');
                currentBtn.addClass(inverseIconClass);
                currentBtn.data('icon-class', inverseIconClass);
                currentBtn.data('inverse-icon-class', iconClass);
            }
        });
    });

    $('#tool-table-body').on('click', '.denied-btn', function () {
        var currentBtn = $(this);
        var orderedToolId = $(this).data('ordered-tool-id');
        var roleId = $(this).data('role-id');
        var action = $(this).data('decoder-name');
        var iconClass = $(this).data('icon-class');
        var inverseIconClass = $(this).data('inverse-icon-class');
        
        $.ajax({
            url: Routing.generate(
                'claro_workspace_inverse_ordered_tool_right',
                {
                    'orderedTool': orderedToolId,
                    'role': roleId,
                    'action': action
                }
            ),
            type: 'POST',
            success: function() {
                currentBtn.removeClass('denied-btn');
                currentBtn.removeClass('text-danger');
                currentBtn.removeClass(iconClass);
                currentBtn.addClass('granted-btn');
                currentBtn.addClass('text-success');
                currentBtn.addClass(inverseIconClass);
                currentBtn.data('icon-class', inverseIconClass);
                currentBtn.data('inverse-icon-class', iconClass);
            }
        });
    });

    var editName = function(tool) {
        $('#tool-' + tool.tool_id + '-name').html(tool.name);
    }
})();

