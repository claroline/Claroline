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
            },
            'error': function ( xhr, textStatus) {
                if (xhr.status === 400) {//bad request
                    alert('BUG');
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
                    route = Routing.generate('claro_admin_competence_add_sub',{'competenceId': data.id});
                    $('#tree').append('<li class=""><a href="'+route+'">'+data.name+'</a></li>');
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
				$('#tree .panel-body').append(data.tree);
			}
		})
	});
})();