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

    //in template ClarolineCoreBundle:Administration\Competence:competences.html.twig
    $('body').on('click', '#show-add-form', function(event) {
        window.Claroline.Modal.displayForm(
            $(event.currentTarget).attr('data-href'),
            addCompetenceLink,
            function() {},
            'competence-create-form'
        );
    });

    $('.competence-view-btn').on('click', function() {
        var competenceId = $(this).data('competence-id');
        var route = Routing.generate(
            'claro_admin_competence_view',
            {'competence': competenceId}
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
            'claro_admin_competence_edit_form',
            {'competence': competenceId}
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
            'claro_admin_sub_competence_create_form',
            {'parent': competenceNodeId}
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
            'claro_admin_sub_competence_link_form',
            {'parent': competenceNodeId}
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
            'claro_admin_competence_node_delete',
            {'competenceNode': competenceNodeId}
        );
        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('remove_sub_competence_comfirm_message', {}, 'platform'),
            Translator.trans('remove_sub_competence', {}, 'platform')
        );
    });

//    //in template competenceReferential.html.twig
//    $('body').on('click', '#show-referential-comptence-form', function(event) {
//        event.preventDefault();
//        console.debug($(this).attr('href'));
//        window.Claroline.Modal.displayForm(
//            $(this).attr('href'),
//            addCompetenceHierachyLink,
//            function() {},
//            'competence-create-form'
//        );
//    })

    $('body').on('click', '.delete-competence', function(event) {
        event.preventDefault();
        var element = $(this).parent();
        window.Claroline.Modal.confirmRequest(
            $(event.currentTarget).attr('href'),
            removeEvent,
            element,
            Translator.trans('remove_competence_comfirm', {}, 'competence'),
            Translator.trans('remove_competence', {}, 'competence')
        );
    });

//    $('body').on('click', '.remove-competence-node', function(event) {
//        event.preventDefault();
//        var element = $(this).parent();
//        var route = $(this).attr('href');
//        window.Claroline.Modal.confirmRequest(
//            route,
//            refreshPage,
//            element,
//            Translator.trans('remove_sub_competence_comfirm_message', {}, 'platform'),
//            Translator.trans('remove_sub_competence', {}, 'platform')
//        );
//    });

//    $('#move').click(function(){
//        var data = new FormData($('#myForm')[0]);
//        var parameters = {};
//        var competences = [];
//        var nbcpt = $('#myForm input:checked').length;
//
//        if (nbcpt > 0) {
//            $('#myForm input:checked').each(function (i, element) {
//                competences[i] = element.value;
//                i++;
//            });
//            parameters.competences = competences;
//            var parentId = $('#competences_link_form_parent').val();
//            parameters.parentId = parentId;
//    	}
//
//    	var route = Routing.generate('claro_admin_competence_move',{'parent': parentId});
//    	route += '?'+ $.param(parameters);
//        $.ajax({
//            'url': route,
//            'type': 'POST',
//            'success': function (data, textStatus, xhr) {
//                if (xhr.status === 200) {
//                    window.location.replace(Routing.generate('claro_admin_competences'));
//                }
//            }
//        });
//    });
//
//    $('#link_node').click(function () {
//        var parameters = {};
//        var id = $('.id').attr('data-root');
//        parameters.competenceId = id;
//        var route = Routing.generate('claro_admin_competences_link', {'competenceId':id });
//        $.ajax({
//            'url':route+'?'+$.param(parameters),
//            'type': 'POST',
//            'data': {'competenceId':id },
//            'success': function(data) {
//            }
//        });
//    });
//	/***************************** competences subscription **************************************/
//
//    $('#chkAll').click(function(event) {  //on click
//        if(this.checked) { // check select status
//            $('.cpt').each(function() { //loop through each checkbox
//                this.checked = true;  //select all checkboxes with class "cpt"
//            });
//            $('.cpt-button').removeAttr('disabled');
//        } else {
//            $('.cpt').each(function() { //loop through each checkbox
//                this.checked = false; //deselect all checkboxes with class "cpt"
//            });
//        }
//    });
//
//    $('.cpt-button').click(function(){
//    	var parameters = {};
//    	var nbcpt = $('.cpt:checked').length;
//    	var competences = [];
//    	if (nbcpt > 0) {
//            $('.cpt:checked').each(function (i, element) {
//                competences[i] = element.value;
//                i++;
//        	});
//        	parameters.competences = competences;
//            if (context === 'workspace') {
//                var route = Routing.generate(
//                    'claro_workspace_competence_subcription_users_form',{'workspaceId':$('#workspace').attr('data-workspace')}
//                    );
//            } else {
//    	    	var route  = Routing.generate('claro_admin_competence_subcription_users_form');
//            }
//	    	document.location.href = route+'?'+$.param(parameters);
//        } else {
//        	alert('no checkboxes selected');
//        }
//    });
//
//    $('.cpt').click(function(){
//        if ($('.cpt:checked').prop('checked')) {
//            $('.cpt-button').removeAttr('disabled');
//        } else {
//            $('.cpt-button').attr('disabled', 'disabled');
//        }
//    });
//
//    $('.subscribe-subjects-button').click(function () {
//        var parameters = {};
//        var route;
//        var i = 0;
//        var j = 0;
//        var k = 0;
//        var subjects = [];
//        var subjectType = $('#registration-list-div').attr('subject-type');
//        var nbSubjects = $('.chk-subject:checked').length;
//        var competences = [];
//
//        $('.cpt-users').each(function(i,element){
//            competences[i] = element.value;
//        });
//        parameters.competences = competences;
//        if (nbSubjects > 0) {
//            $('.chk-subject:checked').each(function (index, element) {
//                subjects[i] = element.value;
//                i++;
//            });
//            parameters.subjectIds = subjects;
//
//            if (subjectType === 'user') {
//                if (context === 'workspace') {
//                    route = Routing.generate(
//                        'claro_workspace_competence_subcription_users',{'workspaceId': $('#workspace').attr('data-workspace')}
//                    );
//
//                } else {
//                    route = Routing.generate(
//                        'claro_admin_competence_subcription_users'
//                    );
//                }
//            }
//            else {
//                route = Routing.generate(
//                    'claro_admin_subscribe_groups_to_one_workspace'
//                );
//            }
//            route += '?' + $.param(parameters);
//
//            $.ajax({
//                url: route,
//                statusCode: {
//                    200: function () {
//                        if (context === 'workspace') {
//                            document.location.href = Routing.generate('claro_workspace_competences_list_users',{'workspaceId': $('#workspace').attr('data-workspace')});
//                        } else {
//                           document.location.href = Routing.generate('claro_admin_competences_list_users');
//                        }
//                    },
//                    type: 'POST'
//                }
//            });
//        }
//    });
//    $('.delete-user').click(function(){
//        var nbSubjects = $('.cpt:checked').length;
//        var users = [];
//        $('.cpt:checked').each(function(i,element){
//            users[i] = $(element).attr('data-id');
//        });
//        var parameters = {};
//        parameters.users = users;
//        var competenceRoot = $('#root').attr('data-id');
//        parameters.root = competenceRoot;
//        var route =  Routing.generate('claro_admin_competences_link',{'competence': competenceRoot});
//        route += '?' + $.param(parameters);
//        if ( nbSubjects > 0) {
//            $.ajax({
//                url: route,
//                statusCode: {
//                    200: function () {
//                        document.location.href = Routing.generate('claro_admin_competences_list_users');
//                    },
//                    type: 'POST'
//                }
//            });
//        }
//    });

    var addCompetenceLink = function(competenceNode) {
        var html = Twig.render(CompetenceItem, {'competenceNode': competenceNode});
        $('#ul-competence').append(html);
    }

//    var addCompetenceHierachyLink = function(competence) {
//        var html = Twig.render(CompetenceNodeItem,{'root': competence})
//    }
//
    var removeEvent = function(competence, element) {
        $(element).remove();
    }

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    }
})();
