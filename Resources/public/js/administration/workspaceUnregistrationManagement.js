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
    var routeName = (type === 'user') ?
        'claro_admin_workspace_users_unregistration_management' :
        'claro_admin_workspace_groups_unregistration_management';
    var workspaceId = $('#datas-box').data('workspace-id');
    var currentSearch = $('#datas-box').data('search');
    var currentMax = $('#datas-box').data('max');
    var currentOrderedBy = $('#datas-box').data('ordered-by');
    var currentOrder = $('#datas-box').data('order');

    function checkSelection()
    {
        if ($('.subject-chk:checked').length > 0) {
            $('#unregister-selected-subjects-btn').removeClass('disabled');
        } else {
            $('#unregister-selected-subjects-btn').addClass('disabled');
        }
    }
    
    $('#subjects-table-body').on('change', '.subject-chk', function () {
        checkSelection();
    });
    
    $('#all-subjects-chk').on('change', function () {
        var checked = $(this).prop('checked');
        
        if (checked) {
            $('.subject-chk').prop('checked', true);
        } else {
            $('.subject-chk').prop('checked', false);
        }
        checkSelection();
    });

    $('#search-subjects-btn').on('click', function () {
        var search = $('#search-subjects-input').val();
        var route = Routing.generate(
            routeName,
            {
                'workspace': workspaceId,
                'search': search,
                'max': currentMax,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );

        window.location.href = route;
    });

    $('#search-subjects-input').keypress(function(e) {
        if (e.keyCode === 13) {
            var search = $(this).val();
            var route = Routing.generate(
                routeName,
                {
                    'workspace': workspaceId,
                    'search': search,
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            );

            window.location.href = route;
        }
    });
    
    $('#max-select').on('change', function () {
        var max = $(this).val();
        var route = Routing.generate(
            routeName,
            {
                'workspace': workspaceId,
                'search': currentSearch,
                'max': max,
                'orderedBy': currentOrderedBy,
                'order': currentOrder
            }
        );
        window.location = route;
    });
    
    $('#subjects-table-body').on('click', '.remove-role-btn', function () {
        var roleElement = $(this).parent('.role-element');
        var subjectId = $(this).data('subject-id');
        var roleId = $(this).data('role-id');
        var route = (type === 'user') ?
            Routing.generate(
                'claro_workspace_remove_role_from_user',
                {
                    'workspace': workspaceId,
                    'user': subjectId,
                    'role': roleId
                }
            ) :
            Routing.generate(
                'claro_workspace_remove_role_from_group',
                {
                    'workspace': workspaceId,
                    'group': subjectId,
                    'role': roleId
                }
            );
        
        $.ajax({
            url: route,
            type: 'DELETE',
            success: function () {
                roleElement.remove();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                window.Claroline.Modal.hide();
                window.Claroline.Modal.simpleContainer(
                    Translator.trans('error', {}, 'platform'),
                    jqXHR.responseJSON.message
                );
            }
        });
    });
    
    $('#unregister-selected-subjects-btn').on('click', function () {
        var nbCheckedSubjects = $('.subject-chk:checked').length;
        var subjectIds = [];
        
        if (nbCheckedSubjects > 0) {
            var unregisterRouteName = (type === 'user') ?
                'claro_admin_unsubscribe_users_from_workspace' :
                'claro_admin_unsubscribe_groups_from_workspace';
            var parameters = {};
            var i = 0;
            var unregistrationMsg;
            
            if (type === 'user') {
                unregistrationMsg = (nbCheckedSubjects > 1) ?
                    Translator.trans(
                        'unregister_user_s_confirm_message',
                        {'count': nbCheckedSubjects},
                        'platform'
                    ) :
                    Translator.trans(
                        'unregister_user_confirm_message',
                        {'count': nbCheckedSubjects},
                        'platform'
                    );
            } else {
                unregistrationMsg = (nbCheckedSubjects > 1) ?
                    Translator.trans(
                        'unregister_group_s_confirm_message',
                        {'count': nbCheckedSubjects},
                        'platform'
                    ) :
                    Translator.trans(
                        'unregister_group_confirm_message',
                        {'count': nbCheckedSubjects},
                        'platform'
                    );
            }
            
            $('.subject-chk:checked').each(function (index, element) {
                subjectIds[i] = element.value;
                i++;
            });
            parameters.subjectIds = subjectIds;
            var route = Routing.generate(
                unregisterRouteName,
                {'workspace': workspaceId}
            );
            route += '?' + $.param(parameters);

            window.Claroline.Modal.confirmRequest(
                route,
                reloadPage,
                null,
                unregistrationMsg,
                Translator.trans('unregister', {}, 'platform')
            );
        }
    });
    
    checkSelection();
    
    var reloadPage = function () {
        window.location.reload();
    };
})();