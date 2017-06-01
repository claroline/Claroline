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

    window.Claroline.ResourcePicker = {};
    var manager = window.Claroline.ResourceManager;
    var common = window.Claroline.Common;
    var modal = window.Claroline.Modal;
    var translator = window.Translator;
    var routing =  window.Routing;
    var activePicker = null;
    var timeoutId = null;
    var defaultCallback = function (nodes) {
        var nodeId = _.keys(nodes)[0];
        var name = nodes[_.keys(nodes)][0];
        var type = nodes[_.keys(nodes)][1];
        $(activePicker).prev().val(nodeId);
        $(activePicker).prev().data('name', name);
        $(activePicker).prev().data('type', type);
        $(activePicker).prev().trigger('change');
        $('input', activePicker).val(name);
        checkView();
    };


    /**
     * Initializes every resource input on the page.
     */
    window.Claroline.ResourcePicker.initialize = function (id) {
        var pickerName = 'formResourcePicker';
        var field = $('#' + id);
        var element = field.next('.input-group');
        var customParameters = processCustomParameters(field.data());

        $('input.form-control', element)
            .on('focus', function () {
                activePicker = this.parentNode;
                openPicker(pickerName, customParameters);
            });

        var inputGroupButton = $('.input-group-btn', element);

        $('button.resource-browse', inputGroupButton)
            .on('click', function () {
                activePicker = this.parentNode.parentNode;
                openPicker(pickerName, customParameters);
            });

        $('button.resource-download', inputGroupButton)
            .on('click', function () {
                activePicker = this.parentNode.parentNode;
                modal.fromRoute('claro_upload_modal', null, function (element) {
                    element.on('click', '.resourcePicker', function () {
                        openPicker(pickerName, customParameters);
                    })
                        .on('click', '.filePicker', function () {
                            $('#file_form_file').click();
                        })
                        .on('change', '#file_form_file', function () {
                            common.uploadfile(this, element, defaultCallback);
                        });
                });
            });

        var name = field.data('name');

        if (name) {
            console.log(name, element)
            $('input', element).val(name);
        }

        if (field.next().hasClass('help-block')) {
            field.next().appendTo(element);
        }

        field.addClass('resource-picker-done').addClass('hide');
        checkView(element);
    };

    function processCustomParameters(datas) {
        var customParameters = null;

        var simpleParameterList = [
            'restrictForOwner',
            'isPickerMultiSelectAllowed',
            'isDirectorySelectionAllowed',
            'allowRootSelection'
        ];
        var arrayParameterList = [
            'typeBlackList',
            'typeWhiteList'
        ];

        for (var i = 0; i < simpleParameterList.length; i++) {
            var simpleParameterName = simpleParameterList[i];
            if (undefined !== datas[simpleParameterName]) {
                customParameters = customParameters || {};
                customParameters[simpleParameterName] = datas[simpleParameterName];
            }
        }

        for (var i = 0; i < arrayParameterList.length; i++) {
            var arrayParameterName = arrayParameterList[i];
            if (undefined !== datas[arrayParameterName]) {
                customParameters = customParameters || {};
                customParameters[arrayParameterName] = datas[arrayParameterName].split(',');
            }
        }

        return customParameters;
    }

    /**
     * Opens a resource picker.
     */
    function openPicker(pickerName, customParameters) {
        if (!manager.hasPicker(pickerName)) {
            var parameters = {
                callback: defaultCallback
            };

            if (customParameters) {
                _.keys(customParameters).forEach(function (parameter) {
                    parameters[parameter] = customParameters[parameter];
                });
            }

            manager.createPicker(pickerName, parameters, true);
        } else {
            manager.picker(pickerName, 'open');
        }
    };

    /**
     * Checks if a resource was selected and if so, enables the "view" button
     */
    function checkView(targetPicker) {
        activePicker = targetPicker || activePicker;

        var nodeId = $(activePicker).prev().val();
        var type = $(activePicker).prev().data('type');

        if (nodeId && type) {
            $('.resource-view', activePicker).removeClass('disabled').attr(
                'href',
                routing.generate('claro_resource_open', {
                    resourceType: type,
                    node: nodeId
                })
            );
        } else {
            $('.resource-view', activePicker).attr('href', '').addClass('disabled');
        }
    };
})();
