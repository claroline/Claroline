/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

'use_strict';

var resourceManager = window.Claroline.ResourceManager;
var rootId  = $('#data').attr('data-root-id');
var modelId = $('#data').attr('data-model-id');

var pickerCopy = resourceManager.createPicker(
    'copy',
    {
        'isPickerMultiSelectAllowed': true,
        'directoryId': rootId,
        'callback': function (nodes) {
            var ids = [];
            for (var id in nodes) {
                ids.push(id);
            }
            var queryString = {};
            queryString.nodeIds = ids;
            var url = Routing.generate('ws_model_resource_copy_add', {'model': modelId}) + '?' + $.param(queryString);
            $.ajax({
               url: url,
               success: function (data) {
                   for (var i = 0; i < data.length; i++) {
                       console.debug(data[i].resourceModelId);
                       console.debug(data[i].name);
                       var html = Twig.render(ModelResource, {'resourceModelId': data[i].resourceModelId, 'name': data[i].name});
                       console.debug(html);
                       $('#list-resnode-copy').append(html);
                   }
               }
            });
        }
    },
    false
);

var pickerLink = resourceManager.createPicker(
    'link',
    {
        'isPickerMultiSelectAllowed': true,
        'directoryId': rootId,
        'isDirectorySelectionAllowed': false,
        'callback': function (nodes) {
            var ids = [];
            for (var id in nodes) {
                ids.push(id);
            }
            var queryString = {};
            queryString.nodeIds = ids;
            var url = Routing.generate('ws_model_resource_link_add', {'model': modelId}) + '?' + $.param(queryString);
            $.ajax({
                url: url,
                success: function (data) {
                    for (var i = 0; i < data.length; i++) {
                        console.debug(data[i].resourceModelId);
                        console.debug(data[i].name);
                        var html = Twig.render(ModelResource, {'resourceModelId': data[i].resourceModelId, 'name': data[i].name});
                        console.debug(html);
                        $('#list-resnode-link').append(html);
                    }
                }
            });
        }
    },
    false
);

$('#add-resnode-copy').on('click', function (event) {
    resourceManager.picker('copy', 'open');
});

$('#add-resnode-link').on('click', function (event) {
    resourceManager.picker('link', 'open');
});

$('#add-tab').on('click', function (event) {
    event.preventDefault();
    var url = $(event.currentTarget).attr('href');
    $.ajax({
        url: url,
        success: function (data) {
            window.Claroline.Modal.confirmContainer(Translator.get('platform:add_tab'), data)
                .on('click', '.btn-primary', function () {
                    var parameters = {};
                    var array = [];
                    var i = 0;
                    $('.hometab-chk:checked').each(function (index, element) {

                        if (array.indexOf(element.value) === -1) {
                            array[i] = element.value;
                            i++;
                        }
                    });
                    parameters.ids = array;
                    route = Routing.generate('ws_model_homeTabs_model_link', {'model': modelId});
                    route += '?' + $.param(parameters);
                    
                    $.ajax({
                        url: route,
                        type: 'GET'
                    });
                });
        },
        type: 'GET'
    });
})

$('body').on('click', '.delete-resourceModel', function (event) {
    event.preventDefault();
    window.Claroline.Modal.confirmRequest(
        $(event.currentTarget).attr('href'),
        removeResourceElement,
        $(event.currentTarget).parent(),
        Translator.get('platform:remove_resource_from_model_confirm'),
        Translator.get('platform:remove_resource_from_model')
    );
});

var removeResourceElement = function (event, successParameter, data) {
    successParameter.remove();
}