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

    window.Claroline.ResourcePicker = {
        'domChange': null
    };

    var picker = window.Claroline.ResourcePicker;
    var manager = window.Claroline.ResourceManager;
    var common = window.Claroline.Common;
    var modal = window.Claroline.Modal;
    var translator = window.Translator;
    var routing =  window.Routing;

    /**
     * Add picker element
     */
    picker.addPicker = function (element)
    {
        return $(element).append(
            common.createElement('div', 'input-group').append(
                common.createElement('input', 'form-control')
                .css('cursor', 'pointer')
                .attr('type', 'text')
                .attr('placeholder', translator.get('platform:add_resource'))
                .on('focus', function () {
                    picker.activePicker = this.parentNode;
                    picker.open(picker.callBack);
                })
            )
            .append(
                common.createElement('span', 'input-group-btn')
                .append(
                    common.createElement('a', 'btn btn-default disabled resource-view')
                    .append(common.createElement('i', 'icon-eye-open'))
                    .attr('title', translator.get('platform:see'))
                    .attr('data-toggle', 'tooltip')
                    .attr('target', '_blank')
                    .css('margin', '0')
                )
                .append(
                    common.createElement('a', 'btn btn-default')
                    .append(common.createElement('i', 'icon-folder-open'))
                    .attr('title', translator.get('platform:resources'))
                    .attr('data-toggle', 'tooltip')
                    .css('margin', '0')
                    .on('click', function () {
                        picker.activePicker = this.parentNode.parentNode;
                        picker.open(picker.callBack);
                    })
                )
                .append(
                    common.createElement('a', 'btn btn-default')
                    .append(common.createElement('i', 'icon-file'))
                    .attr('title', translator.get('platform:upload'))
                    .attr('data-toggle', 'tooltip')
                    .css('margin', '0')
                    .on('click', function () {
                        picker.activePicker = this.parentNode.parentNode;
                        modal.fromRoute('claro_upload_modal', null, function (element) {
                            element.on('click', '.resourcePicker', function () {
                                picker.open(picker.callBack);
                            })
                            .on('click', '.filePicker', function () {
                                $('#file_form_file').click();
                            })
                            .on('change', '#file_form_file', function () {
                                common.uploadfile(this, element, picker.callBack);
                            });
                        });
                    })
                )
            )
        );
    };

    /**
     * Check if there is a resource selected and add href to the view link
     */
    picker.checkView = function (activePicker)
    {
        picker.activePicker = activePicker !== undefined ? activePicker : picker.activePicker;

        var nodeId = $(picker.activePicker).prev().val();
        var type = $(picker.activePicker).prev().data('type');

        if (nodeId && type) {
            $('.resource-view', picker.activePicker).removeClass('disabled').attr(
                'href', routing.generate('claro_resource_open', {'resourceType': type, 'node': nodeId})
            );
        } else {
            $('.resource-view', picker.activePicker).attr('href', '').addClass('disabled');
        }
    };

    /**
     * Default call back for a resource picker
     */
    picker.callBack = function (nodes)
    {
        console.log(nodes);

        var nodeId = _.keys(nodes)[0];
        var name = nodes[_.keys(nodes)][0];
        var type = nodes[_.keys(nodes)][1];

        $(picker.activePicker).prev().val(nodeId);
        $(picker.activePicker).prev().data('name', name);
        $(picker.activePicker).prev().data('type', type);
        $('input', picker.activePicker).val(name);

        picker.checkView();
    };

    /**
     * Open a resource picker.
     */
    picker.open = function (callBack)
    {
        if ($('#resourcePicker').get(0) === undefined) {
            $('body').append('<div id="resourcePicker"></div>');
            $.ajax(routing.generate('claro_resource_init'))
                .done(function (data) {
                    var resourceInit = JSON.parse(data);
                    resourceInit.parentElement = $('#resourcePicker');
                    resourceInit.isPickerMultiSelectAllowed = false;
                    resourceInit.isPickerOnly = true;
                    resourceInit.pickerCallback = callBack;
                    manager.initialize(resourceInit);
                    manager.picker('open');
                })
                .error(function () {
                    modal.error();
                });
        } else {
            manager.picker('open');
        }
    };

    /**
     * Initialize function for resource picker input.
     */
    picker.initialize = function ()
    {
        $('input.resource-picker:not(.resource-picker-done)').each(function () {
            var element = picker.addPicker(this.parentNode);
            var name = $(this).data('name');

            if (name) {
                $('.input-group input', element).val(name);
            }

            $(this).addClass('resource-picker-done');
            picker.checkView($('.input-group', element));
        });
    };

    /** Events **/

    $('body').bind('ajaxComplete', function () {
        picker.initialize();
    })
    .on('click', 'input.resourcePickerName, input.resourcePickerButton', function () {
        picker.select(this);
    })
    .bind('DOMSubtreeModified', function () {
        clearTimeout(picker.domChange);
        picker.domChange = setTimeout(picker.initialize, 10);
    });

    $(document).ready(function () {
        picker.initialize();
    });
}());
