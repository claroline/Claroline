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

    var workspaceId = $('#workspace-data-element').data('workspace-id');

    $('#create-learning-outcomes-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_workspace_learning_outcomes_create_form', {'workspace': workspaceId}),
            refreshPage,
            function() {}
        );
    });

    $('.edit-learning-outcomes-btn').on('click', function () {
        var competenceId = $(this).data('competence-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('claro_workspace_competence_edit_form', {'workspace': workspaceId, 'competence': competenceId}),
            refreshPage,
            function() {}
        );
    });

    $('.delete-learning-outcomes-btn').on('click', function () {
        var competenceNodeId = $(this).data('competence-node-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_workspace_competence_node_delete', {'workspace': workspaceId, 'competenceNode': competenceNodeId}),
            refreshPage,
            null,
            Translator.trans('delete_learning_outcomes_confirm_message', {}, 'platform'),
            Translator.trans('delete_learning_outcomes', {}, 'platform')
        );
    });

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    }
})();
