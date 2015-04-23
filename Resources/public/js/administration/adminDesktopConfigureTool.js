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
    
    var type = $('#datas-box').data('type');
    
    $('.visibility-btn').on('click', function () {
        var element = $(this);
        var orderedToolId = element.data('ordered-tool-id');
        
        $.ajax({
            url: Routing.generate(
                'claro_admin_ordered_tool_toggle_visibility',
                {
                    'orderedTool': orderedToolId
                }
            ),
            type: 'POST',
            success: function() {
                
                if (element.hasClass('fa-eye')) {
                    element.removeClass('fa-eye');
                    element.addClass('fa-eye-slash');
                } else {
                    element.removeClass('fa-eye-slash');
                    element.addClass('fa-eye');
                }
            }
        });
    });
    
    $('.lock-btn').on('click', function () {
        var element = $(this);
        var orderedToolId = element.data('ordered-tool-id');
        
        $.ajax({
            url: Routing.generate(
                'claro_admin_ordered_tool_toggle_lock',
                {
                    'orderedTool': orderedToolId
                }
            ),
            type: 'POST',
            success: function() {
                
                if (element.hasClass('fa-lock')) {
                    element.removeClass('fa-lock');
                    element.addClass('fa-unlock');
                } else {
                    element.removeClass('fa-unlock');
                    element.addClass('fa-lock');
                }
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
            var nextOrderedToolId = -1;
            var nextElement = $(ui.item).next();
            
            if (nextElement !== undefined && nextElement.hasClass('row-tool-config')) {
                nextOrderedToolId = nextElement.data('ordered-tool-id');
            }
            
            $.ajax({
                url: Routing.generate(
                    'claro_admin_desktop_update_ordered_tool_order',
                    {
                        'orderedTool': orderedToolId,
                        'nextOrderedToolId': nextOrderedToolId,
                        'type': type
                    }
                ),
                type: 'POST'
            });
        }
    });
})();