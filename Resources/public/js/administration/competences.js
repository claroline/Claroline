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
        	var rootId = $('#competences_link_form_root').val();
        	var parentId = $('#competences_link_form_parent').val();
        	parameters.rootId = rootId;
        	parameters.parentId = parentId;
    	}
    	//var route = $('#link').attr('data-route');
    	var route = Routing.generate('claro_admin_competence_link',{'rootId': rootId, 'parentId': parentId});
    	console.debug('parent: '+parentId+' rootId:'+rootId);
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
})();