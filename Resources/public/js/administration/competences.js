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
    window.Competence = window.Competence || {};
    var competence = window.Claroline.Competence = {};
    var context ;
    competence.initialize = function (context2) {
        context = context2 || 'desktop';
    }

    $('#link').click(function(){
        var data = new FormData($('#myForm')[0]);
        var parameters = {};
        var competences = [];
        var nbcpt = $('#myForm input:checked').length;

        if (nbcpt > 0) {
            $('#myForm input:checked').each(function (i, element) {
                competences[i] = element.value;
                i++;
            });
        	parameters.competences = competences;
        	var parentId = $('#competences_link_form_parent').val();
        	parameters.parentId = parentId;
    	}

    	var route = Routing.generate('claro_admin_competence_move',{'parentId': parentId});
    	route += '?'+$.param(parameters);
        $.ajax({
            'url': route,
            'type': 'POST',
            'success': function (data, textStatus, xhr) {
                if (xhr.status === 200) {
                    window.location.replace(Routing.generate('claro_admin_competences'));
                }
            }
        });
    });

	$('#save').click(function () {
        $('#save').attr('disabled', 'disabled');
        var url = $('#myForm').attr('action');
        var route;
        $.ajax({
            'url': url,
            'type': 'POST',
            'data':  new FormData($('#myForm')[0]),
            'processData': false,
            'contentType': false,
            'success': function (data, textStatus, xhr) {
                if (xhr.status === 200) {
                    $('#myModal').modal('hide');
                    $('#save').removeAttr('disabled');
                    route = Routing.generate('claro_admin_competence_show_referential',{'competenceId': data.id});
                    $('.nav-stacked').append('<li class=""><a href="'+route+'">'+data.name+'</a></li>');;
                }
            },
            'error': function ( xhr, textStatus) {
                if (xhr.status === 400) {//bad request
                    alert(Translator.get('agenda' + ':' + 'date_invalid'));
                    $('#save').removeAttr('disabled');
                } 
            }
        });
    });
	$('#see').click(function () {
		var parameters = {};
		var id = $('#see').attr('data-id');
		parameters.competenceId = id;
		var route = Routing.generate('claro_admin_competence_full_hierarchy', {'competenceId':id });
		
		$.ajax( {
			'url':route+'?'+$.param(parameters),
			'type': 'POST',
			'data': {'competenceId':id },
			'success': function(data) {
			   if($('#tree .panel-body').empty())
			   {
					$('#tree .panel-body').append(data.tree);
			   }
			}
		});
	});
	/***************************** competences subscription **************************************/

    $('#chkAll').click(function(event) {  //on click 
        if(this.checked) { // check select status
            $('.cpt').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "cpt"               
            });
            $('.cpt-button').removeAttr('disabled');
        } else {
            $('.cpt').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "cpt"                       
            });         
        }
    });

    $('.cpt-button').click(function(){
    	var parameters = {};
    	var nbcpt = $('.cpt:checked').length;
    	var competences = [];
    	if (nbcpt > 0) {
            $('.cpt:checked').each(function (i, element) {
                competences[i] = element.value;
                i++;
        	});
        	parameters.competences = competences;
            if (context === 'workspace') {
                var route = Routing.generate(
                    'claro_workspace_competence_subcription_users_form',{'workspaceId':$('#workspace').attr('data-workspace')}
                    );
            } else {
    	    	var route  = Routing.generate('claro_admin_competence_subcription_users_form');
            }
	    	document.location.href = route+'?'+$.param(parameters);
        } else {
        	alert('no checkboxes selected');
        }
    });

    $('.cpt').click(function(){
        if ($('.cpt:checked').prop('checked')) {
            $('.cpt-button').removeAttr('disabled');
        } else {
            $('.cpt-button').attr('disabled', 'disabled');
        }
    });

	$('.subscribe-subjects-button').click(function () {
        var parameters = {};
        var route;
        var i = 0;
        var j = 0;
        var k = 0;
        var subjects = [];
        var subjectType = $('#registration-list-div').attr('subject-type');
        var nbSubjects = $('.chk-subject:checked').length;
        var competences = [];

        $('.cpt-users').each(function(i,element){
            competences[i] = element.value;
        });
        parameters.competences = competences;
        if (nbSubjects > 0) {
            $('.chk-subject:checked').each(function (index, element) {
                subjects[i] = element.value;
                i++;
            });
            parameters.subjectIds = subjects;

            if (subjectType === 'user') {
                if (context === 'workspace') {
                    route = Routing.generate(
                        'claro_workspace_competence_subcription_users',{'workspaceId': $('#workspace').attr('data-workspace')}
                    ); 
                    
                } else {
                    route = Routing.generate(
                        'claro_workspace_admin_subcription_users'
                    );
                }
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
                    200: function () {
                        if (context === 'workspace') {
                            document.location.href = Routing.generate('claro_workspace_competences_list_users',{'workspaceId': $('#workspace').attr('data-workspace')});
                        } else {
                           document.location.href = Routing.generate('claro_admin_competences_list_users');
                        }
                    },
                    type: 'POST'
                }
            });
        }
    });
    $('.delete-user').click(function(){
        var nbSubjects = $('.cpt:checked').length;
        var users = [];
        $('.cpt:checked').each(function(i,element){
            users[i] = $(element).attr('data-id');
        });
        var parameters = {};
        parameters.users = users;
        var competenceRoot = $('#root').attr('data-id');
        parameters.root = competenceRoot;
        var route =  Routing.generate('claro_admin_competence_unsubscription_users',{'root': competenceRoot});
        route += '?' + $.param(parameters);
        if ( nbSubjects > 0) {
            $.ajax({
                url: route,
                statusCode: {
                    200: function () {
                        document.location.href = Routing.generate('claro_admin_competences_list_users');
                    },
                    type: 'POST'
                }
            });
        }
    });
})();