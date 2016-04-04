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

    function getPage(tab)
    {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    function getSearch(tab)
    {
        var search = '';

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'search') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    search = tab[i + 1];
                }
                break;
            }
        }

        return search;
    }

    function activateRegisterButton()
    {
        var nbUsers = $('.chk-subject:checked').length;
        var selectedroles = $('#roles-list').val();
        var nbRoles = selectedroles === null ? 0 : selectedroles.length;

        if (nbUsers > 0 && nbRoles > 0) {
            $('.subscribe-subjects-button').attr('disabled', false);
        }
        else {
            $('.subscribe-subjects-button').attr('disabled', 'disabled');
        }
    }

    function initEvents()
    {
        $('#list-pager').on('click', '.pagination > ul > li > a', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var element = event.currentTarget;
            var url = $(element).attr('href');
            var route;

            if (url !== '#') {
                var urlTab = url.split('/');
                var page = getPage(urlTab);
                var search = getSearch(urlTab);

                if (search !== '') {
                    route = Routing.generate(
                        'claro_users_list_registration_pager_search',
                        {'page': page, 'search': search}
                    );
                } else {
                    route = Routing.generate(
                        'claro_users_list_registration_pager',
                        {'page': page}
                    );
                }

                $.ajax({
                    url: route,
                    success: function (result) {
                        var source = $(element).parent().parent().parent().parent();
                        $(source).children().remove();
                        $(source).append(result);
                    },
                    type: 'GET'
                });
            }
        });

        $('#registration-list-div').on('click', '.chk-subject, .role-option', function () {
            activateRegisterButton();
        });

        $('.subscribe-subjects-button').click(function () {
            var parameters = {};
            var route;
            var i = 0;
            var j = 0;
            var k = 0;
            var subjects = [];
            var workspaces = [];
            var roles = [];
            var subjectType = $('#registration-list-div').attr('subject-type');
            var nbSubjects = $('.chk-subject:checked').length;

            if (nbSubjects > 0) {
                $('.chk-subject:checked').each(function (index, element) {
                    subjects[i] = element.value;
                    i++;
                });
                parameters.subjectIds = subjects;

                if ($('#roles-list').attr('nb-workspaces') === '1') {
                    $('.role-option:selected').each(function (index, element) {
                        roles[k] = element.value;
                        k++;
                    });
                    parameters.roleIds = roles;

                    if (subjectType === 'user') {
                        route = Routing.generate(
                            'claro_admin_subscribe_users_to_one_workspace'
                        );
                    }
                    else {
                        route = Routing.generate(
                            'claro_admin_subscribe_groups_to_one_workspace'
                        );
                    }
                    route += '?' + $.param(parameters);

                    $.ajax({
                        url: route,
                        statusCode: {
                            200: function (data) {
                                $('.chk-subject:checked').attr('checked', false);
                                activateRegisterButton();

                                var messages = data.split('-;-');
                                var nbMessages = messages.length - 1;
                                var flashbag = $('#custom-flashbag-ul');

                                for (var i = 0; i < nbMessages; i++) {
                                    flashbag.append('<li>' + messages[i] + '</li>');
                                }
                                $('#custom-flashbag-div').removeClass('hide');
                            }
                        },
                        type: 'POST'
                    });
                }
                else {
                    $('.workspace-option').each(function (index, element) {
                        workspaces[j] = $(element).attr('workspace-id');
                        j++;
                    });
                    parameters.workspaceIds = workspaces;

                    $('.role-option:selected').each(function (index, element) {
                        var role = element.value;

                        if (subjectType === 'user') {
                            route = Routing.generate(
                                'claro_admin_subscribe_users_to_workspaces',
                                {'roleKey': role}
                            );
                        }
                        else {
                            route = Routing.generate(
                                'claro_admin_subscribe_groups_to_workspaces',
                                {'roleKey': role}
                            );
                        }
                        route += '?' + $.param(parameters);

                        $.ajax({
                            url: route,
                            statusCode: {
                                200: function (data) {
                                    $('.chk-subject:checked').attr('checked', false);
                                    activateRegisterButton();

                                    var messages = data.split('-;-');
                                    var nbMessages = messages.length - 1;
                                    var flashbag = $('#custom-flashbag-ul');

                                    for (var i = 0; i < nbMessages; i++) {
                                        flashbag.append('<li>' + messages[i] + '</li>');
                                    }
                                    $('#custom-flashbag-div').removeClass('hide');

                                }
                            },
                            type: 'POST'
                        });
                    });
                }
            }
        });

        $('#flashbag-close-button').click(function () {
            $(this).parent().addClass('hide');
            $('#custom-flashbag-ul').empty();
        });

        $('#search-button').click(function () {
            var search = $('#search-txt').val();
            var subjectType = $('#registration-list-div').attr('subject-type');
            var route;

            if (subjectType === 'user') {
                if (search !== '') {
                    route = Routing.generate(
                        'claro_users_list_registration_pager_search',
                        {'search': search}
                    );
                } else {
                    route = Routing.generate('claro_users_list_registration_pager');
                }
            }
            else {
                if (search !== '') {
                    route = Routing.generate(
                        'claro_groups_list_registration_pager_search',
                        {'search': search}
                    );
                } else {
                    route = Routing.generate('claro_groups_list_registration_pager');
                }
            }

            $.ajax({
                url: route,
                success: function (result) {
                    var source = $('#list-pager');
                    $(source).children().remove();
                    $(source).append(result);
                    activateRegisterButton();
                },
                type: 'GET'
            });

        });
    }

    // When clicking on the Book icon show the modal with the list of selected workspace
    $('body').on('click', '#remove-workspace-from-list', function () {
        $('#remove-workspace-modal-box').modal('show');
    });
    
    // When clicking on the Trash icon in the list of selected workspace 
    $('body').on('click', '.remove-workspace-button', function () {
        var workspaceId = $(this).attr('workspace-id');
        $(this).parent('.workspace-element-for-removal').remove();
        $('#option-workspace-' + workspaceId).remove();
        
        var remainingWorkspaces = $('.workspace-element-for-removal');
        
        // If there is only one workspace left hide the modal and remove the Book icon
        if (remainingWorkspaces.length <= 1) {
            $('#remove-workspace-modal-box').modal('hide');
            $('#remove-workspace-from-list').remove();
        }
    });
    
    initEvents();
})();