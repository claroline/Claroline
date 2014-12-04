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

    $('.competence-view-btn').on('click', function() {
        var competenceId = $(this).data('competence-id');
        var route = Routing.generate(
            'claro_workspace_competence_view',
            {'workspace': workspaceId, 'competence': competenceId}
        );

        window.Claroline.Modal.displayForm(
            route,
            refreshPage,
            function() {}
        );
    });

    $('.competence-edit-btn').on('click', function() {
        var competenceId = $(this).data('competence-id');
        var route = Routing.generate(
            'claro_workspace_competence_edit_form',
            {'workspace': workspaceId, 'competence': competenceId}
        );

        window.Claroline.Modal.displayForm(
            route,
            refreshPage,
            function() {}
        );
    });

    $('.add-sub-competence-btn').on('click', function() {
        var competenceNodeId = $(this).data('competence-node-id');
        var route = Routing.generate(
            'claro_workspace_sub_competence_create_form',
            {'workspace': workspaceId, 'parent': competenceNodeId}
        );
        window.Claroline.Modal.displayForm(
            route,
            refreshPage,
            function() {}
        );
    });

    $('.link-sub-competence-btn').on('click', function() {
        var competenceNodeId = $(this).data('competence-node-id');
        var route = Routing.generate(
            'claro_workspace_sub_competence_link_form',
            {'workspace': workspaceId, 'parent': competenceNodeId}
        );
        window.Claroline.Modal.displayForm(
            route,
            refreshPage,
            function() {}
        );
    });

    $('.remove-competence-node-btn').on('click', function() {
        var competenceNodeId = $(this).data('competence-node-id');
        var route = Routing.generate(
            'claro_workspace_competence_node_delete',
            {'workspace': workspaceId, 'competenceNode': competenceNodeId}
        );
        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('remove_sub_competence_comfirm_message', {}, 'platform'),
            Translator.trans('remove_sub_competence', {}, 'platform')
        );
    });

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    }
})();
