/* global createValidationBox */
/* global ModalWindow */
/* global ValidationFooter */

(function () {
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
     */
    table.initialize = function (parameters) {
        var currentAction = '';
        createValidationBox();

        $('#search-button').click(function () {
            var search = document.getElementById('search-items-txt').value;
            var route;

            if (search !== '') {
                parameters.route.search.parameters.search = search;
                route = Routing.generate(parameters.route.search.route, parameters.route.search.parameters);
            } else {
                route = Routing.generate(parameters.route.normal.route, parameters.route.normal.parameters);
            }

            window.location.href = route;
        });

        for (var key in parameters.route.action) {
            var btnClass = '.'
                + (parameters.route.action[key].btn === undefined ? 'action-button': parameters.route.action[key].btn);
            $(btnClass).click(function (e) {
                currentAction = $(e.currentTarget).attr('data-action');
                $('.modal').modal('show');
                $('.modal-body').html(Twig.render(parameters.route.action[currentAction].confirmTemplate,
                    {'nbItems': $('.chk-item:checked').length}
                ));
            });
        }

        $('#modal-valid-button').on('click', function () {
            var queryString = {};
            var i = 0;
            var array = [];
            $('.chk-item:checked').each(function (index, element) {
                array[i] = element.value;
                i++;
            });
            queryString.ids = array;
            var route = Routing.generate(
                parameters.route.action[currentAction].route,
                parameters.route.action[currentAction].parameters
            );
            var type =  parameters.route.action[currentAction].type === undefined ?
                'GET':
                parameters.route.action[currentAction].type;
            route += '?' + $.param(queryString);
            $.ajax({
                url: route,
                type: type,
                success: function () {
                    $('.chk-item:checked').each(function (index, element) {
                        $(element).parent().parent().remove();
                    });
                }
            });
            $('.modal').modal('hide');
            $('.modal-body').empty();
        });

        $('#check-all-items').click(function () {
            if ($('#check-all-items').is(':checked')) {
                $(' INPUT[@class=' + 'chk-item' + '][type="checkbox"]').attr('checked', true);
            }
            else {
                $(' INPUT[@class=' + 'chk-item' + '][type="checkbox"]').attr('checked', false);
            }
        });
    };

    createValidationBox = function() {
        var html = Twig.render(ModalWindow, {'footer': Twig.render(ValidationFooter), 'isHidden': true});
        $('body').append(html);
    };
})();
