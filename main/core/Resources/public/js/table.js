/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global createValidationBox */
/* global ModalWindow */
/* global ValidationFooter */
/* global ErrorFooter */

(function() {
    'use strict';

    window.Claroline = window.Claroline || {};
    var table = window.Claroline.Table = {};

    /**
     * Requires the following twigjs files (located in the views/Modal folder):
     * modal.html.twigjs
     * validationFooter.html.twigjs
     *
     * reserved ids and class:
     * #search-button => the search button
     * #search-items-txt => the search box
     * .action-button => the add button by default
     * .chk-item => the checkbox class
     *
     * note: The route defined in parameters.route.search will require "search" parameters.
     * This parameter will be the one taken in the search-item-txt-box.
     *
     * example:
     *
     * parameters.route.normal = {'route': 'route_normal', 'parameters': {'workspaceId': workspaceId }}
     * parameters.route.search = {'route': 'route_search', 'parameters': {'workspaceId': workspaceId }}
     * parameters.route.action.default = {
     *     'route': 'route_action',
     *     'parameters': {'workspaceId': workspaceId },
     *     'type': 'PUT',
     *     'btn': 'btn-default',
     *     'confirmTemplate': templateA
     * }
     * parameters.route.action.other = {
     *     'route': 'route_action',
     *     'parameters': {'workspaceId': workspaceId },
     *     'type': 'PUT',
     *     'btn': 'btn-other',
     *     'confirmTemplate': templateB
     * }
     *
     * You can add a <select> whose id is "max-select". This will send the "max" parameter to the request.
     */
    table.initialize = function(parameters) {
        var currentAction = '';
        createValidationBox();
        createErrorBox();

        // disable all elements with table-control class
        $('.table-control').prop('disabled', true);

        $('#search-button').click(function() {
            var search = document.getElementById('search-items-txt') ?
                    document.getElementById('search-items-txt').value :
                    '';
            var route;

            var max = findMaxPerPage();

            if (max) {
                if (parameters.route.search) {
                    parameters.route.search.parameters.max = max;
                }
                if (parameters.route.normal) {
                    parameters.route.normal.parameters.max = max;
                }
            }

            if (search !== '') {
                parameters.route.search.parameters.search = search;
                route = Routing.generate(parameters.route.search.route, parameters.route.search.parameters);
            } else {
                route = Routing.generate(parameters.route.normal.route, parameters.route.normal.parameters);
            }

            window.location.href = route;
        });

        $('#search-items-txt').keypress(function(e) {

            var max = findMaxPerPage();
            if (max) {
                parameters.route.search.parameters.max = parameters.route.normal.parameters.max = max;
            }

            if (e.keyCode === 13) {
                var search = document.getElementById('search-items-txt').value;
                var route;

                if (search !== '') {
                    parameters.route.search.parameters.search = search;
                    route = Routing.generate(parameters.route.search.route, parameters.route.search.parameters);
                } else {
                    console.debug(parameters);
                    route = Routing.generate(parameters.route.normal.route, parameters.route.normal.parameters);
                }

                window.location.href = route;
            }
        });

        for (var key in parameters.route.action) {
            if (parameters.route.action.hasOwnProperty(key)) {
                var btnClass = '.' +
                    (parameters.route.action[key].btn === undefined ? 'action-button' : parameters.route.action[key].btn);
                $(btnClass).click(function(e) {
                    currentAction = $(e.currentTarget).attr('data-action');
                    var html = Twig.render(parameters.route.action[currentAction].confirmTemplate, {
                        'nbItems': $('.chk-item:checked').length
                    });
                    $('#table-modal .modal-body').html(html);
                    $('#table-modal').modal('show');
                });
            }
        }

        $('#modal-valid-button').on('click', function() {
            if (currentAction) {
                var queryString = {};
                var i = 0;
                var array = [];
                $('.chk-item:checked').each(function(index, element) {
                    array[i] = element.value;
                    i++;
                });
                queryString.ids = array;
                var route = Routing.generate(
                    parameters.route.action[currentAction].route,
                    parameters.route.action[currentAction].parameters
                );
                var type = parameters.route.action[currentAction].type === undefined ?
                    'GET' :
                    parameters.route.action[currentAction].type;
                route += '?' + $.param(queryString);
                $.ajax({
                    url: route,
                    type: type,
                    success: function() {
                        if (parameters.route.action[currentAction].delete) {
                            $('.chk-item:checked').each(function(index, element) {
                                $(element).parent().parent().remove();
                            });
                        }

                        $('.table-control').prop('disabled', true);
                    },
                    error: function(xhr) {
                        $('#error-modal').modal('show');
                        $('#error-modal .modal-body').html(xhr.responseText);
                    }
                });
                $('#table-modal').modal('hide');
                $('.modal-body').empty();
            }
        });

        $('#check-all-items').click(function() {
            if ($('#check-all-items').is(':checked')) {
                $.each($('.chk-item'), function(index, el) {
                    if (!$(el).is(':disabled')) $(el).prop('checked', true);
                });
                // enable .table-control elements
                $('.table-control').prop('disabled', false);
            }
            else {
                $.each($('.chk-item'), function(index, el) {
                    if (!$(el).is(':disabled')) $(el).prop('checked', false);
                });
                 // disable .table-control elements
                $('.table-control').prop('disabled', true);
            }
        });

        // checkboxes click event
        $('.chk-item').on('click', function() {
            $('.table-control').prop('disabled', true);
            // if at least one checkbox is checked
            $('.chk-item').each(function() {
                if ($(this).is(':checked')) {
                    // enable .table-control elements
                    $('.table-control').prop('disabled', false);
                    return true;
                }
            });
        });
    };

    function createErrorBox() {
        var html = Twig.render(
                ModalWindow,
                {'footer': Twig.render(ErrorFooter), 'isHidden': true, 'modalId': 'error-modal', 'body': ''}
        );
        $('body').append(html);
    }

    function createValidationBox() {
        var html = Twig.render(
                ModalWindow,
                {'footer': Twig.render(ValidationFooter), 'isHidden': true, 'modalId': 'table-modal', 'body': ''}
        );
        $('body').append(html);
    }

    function findMaxPerPage() {
        return $('#max-select').val();
    }

})();
