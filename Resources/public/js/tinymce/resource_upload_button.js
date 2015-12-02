(function () {
    'use strict';

    var tinymce = window.tinymce;
    var common = window.Claroline.Common;
    var modal = window.Claroline.Modal;
    var resourceManager = window.Claroline.ResourceManager;
    var translator = window.Translator;

    /**
     * Open a directory picker from a TinyMCE editor.
     */
    var directoryPickerCallBack = function(nodes)
    {
        for (var id in nodes) {
            var val = nodes[id][4];
            var path = nodes[id][3];
        }

        //file_form_destination
        var html = '<option value="' + val + '">' + path + '</option>';
        $('#file_form_destination').append(html);
        $('#file_form_destination').val(val);
    }

    /**
     * Open a resource picker from a TinyMCE editor.
     */
    var directoryPickerOpen = function ()
    {
        if (!resourceManager.hasPicker('tinyMceDirectoryPicker')) {
            resourceManager.createPicker('tinyMceDirectoryPicker', {
                callback: directoryPickerCallBack,
                resourceTypes: ['directory'],
                isDirectorySelectionAllowed: true,
                isPickerMultiSelectAllowed: false
            }, true);
        } else {
            resourceManager.picker('tinyMceDirectoryPicker', 'open');
        }
    };

    tinymce.PluginManager.add('fileUpload', function(editor, url) {
        editor.addButton('fileUpload', {
            'icon': 'none fa fa-file',
            'classes': 'widget btn',
            'tooltip': translator.trans('upload', {}, 'platform'),
            'onclick': function () {
                tinymce.activeEditor = editor;
                modal.fromRoute('claro_upload_modal', null, function (element) {
                    element.on('click', '.resourcePicker', function () {
                        tinymce.claroline.buttons.resourcePickerOpen();
                    })
                    .on('click', '.filePicker', function () {
                        $('#file_form_file').click();
                    })
                    .on('change', '#file_form_destination', function(event) {
                        if ($('#file_form_destination').val() === 'others') {
                            directoryPickerOpen();
                        }
                    })
                    .on('change', '#file_form_file', function () {
                        common.uploadfile(
                            this,
                            element,
                            $('#file_form_destination').val(),
                            tinymce.claroline.buttons.resourcePickerCallBack
                        );
                    })
                });
            }
        });
    });

    tinymce.claroline.plugins.fileUpload = true;
}());
