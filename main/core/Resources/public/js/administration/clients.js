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

    var modal = window.Claroline.Modal;
    var id = '';

    var addClient = function() {
        window.location.reload();
    }

    var removeClientRow = function (event, clientId) {
        $('#client-row-' + clientId).remove();
    };


    $('body')
        .on('click', '#add-client-btn', function(event) {
            var url = Routing.generate('claro_admin_oauth_client_form');
            modal.displayForm(url, addClient, function(){}, 'form_client_creation');
        })
        .on('click', '.show-client-btn', function(event) {
            modal.simpleContainer('', $(event.target).attr('data-client-id') + '_' + $(event.target).attr('data-client-random'));
        })
        .on('click', '.show-secret-btn', function(event) {
            modal.simpleContainer('', $(event.target).attr('data-secret'));
        })
        .on('click', '.show-refresh-btn', function(event) {
            modal.simpleContainer('', $(event.target).attr('data-url'));
        })
        .on('click', '.edit-client-btn', function(event) {
            var clientid = $(event.target).attr('data-client-id');
            var url = Routing.generate('claro_admin_oauth_form_edit', {'client': clientid});
            modal.displayForm(url, addClient, function(){}, 'form_client_update');
        })
        .on('click', '.delete-client-btn', function(event) {
            var clientid = $(event.target).attr('data-client-id');

            modal.confirmRequest(
                Routing.generate(
                    'oauth_client_remove',
                    {'client': clientid}
                ),
                removeClientRow,
                clientid,
                Translator.trans('delete_client_message', {}, 'platform'),
                Translator.trans('delete_client', {}, 'platform')
            );
        })
        .on('click', '.hide-client-btn', function(event) {
            var clientid = $(event.target).attr('data-client-id');

            modal.confirmRequest(
                Routing.generate(
                    'oauth_client_hide',
                    {'client': clientid}
                ),
                removeClientRow,
                clientid,
                Translator.trans('hide_client_message', {}, 'platform'),
                Translator.trans('hide_client', {}, 'platform')
            );
        })
}) ();
