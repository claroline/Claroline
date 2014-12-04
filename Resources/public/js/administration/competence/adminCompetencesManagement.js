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

    $('#create-admin-competence-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_admin_competence_create'),
            refreshPage,
            function() {}
        );
    });

    $('.edit-admin-competence-btn').on('click', function () {
        var competenceId = $(this).data('competence-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('claro_admin_competence_edit', {'competence': competenceId}),
            refreshPage,
            function() {}
        );
    });

    $('.delete-admin-competence-btn').on('click', function () {
        var competenceId = $(this).data('competence-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_admin_competence_delete', {'competence': competenceId}),
            refreshPage,
            null,
            Translator.trans('delete_competence_confirm_message', {}, 'platform'),
            Translator.trans('delete_competence', {}, 'platform')
        );
    });

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    }
})();
