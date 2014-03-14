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

    var tinymce = window.tinymce;
    var home = window.Claroline.Home;
    var modal = window.Claroline.Modal;
    var translator = window.Translator;

    var language = home.locale.trim();
    var contentCSS = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';

    var configTinyMCE = {
        'relative_urls': false,
        'theme': 'modern',
        'language': language,
        'browser_spellcheck': true,
        'autoresize_min_height': 100,
        'autoresize_max_height': 500,
        'content_css': contentCSS,
        'plugins': [
            'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'template paste textcolor emoticons code'
        ],
        'toolbar1': 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | ' +
                    'preview fullscreen resourcePicker fileUpload',
        'toolbar2': 'undo redo | forecolor backcolor emoticons | bullist numlist outdent indent | ' +
                    'link image media print code',
        'paste_preprocess': function (plugin, args) {
            var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery

            home.isValidURL(link, function () {
                home.generatedContent(link, function (data) {
                    tinymce.activeEditor.insertContent('<div>' + data + '</div>');
                    editorChange(tinymce.activeEditor);
                }, false);
            });
        },
        setup: function (editor) {
            editor.on('change', function () {
                if (editor.getElement()) {
                    editor.getElement().value = editor.getContent();
                }
            });
            editor.on('LoadContent', function () {
                editorChange(editor);
            });
            if ($(editor.getElement()).data('resource-picker') !== 'off') {
                editor.addButton('resourcePicker', {
                    'icon': 'none icon-folder-open',
                    'classes': 'widget btn',
                    'tooltip': translator.get('platform:resources'),
                    'onclick': function () {
                        tinymce.activeEditor = editor;
                        resourcePickerOpen();
                    }
                });
                editor.addButton('fileUpload', {
                    'icon': 'none icon-file',
                    'classes': 'widget btn',
                    'tooltip': translator.get('platform:upload'),
                    'onclick': function () {
                        tinymce.activeEditor = editor;
                        modal.fromRoute('claro_upload_modal', null, function (element) {
                            element.on('click', '.resourcePicker', function () {
                                resourcePickerOpen();
                            })
                            .on('click', '.filePicker', function () {
                                $('#file_form_file').click();
                            })
                            .on('change', '#file_form_file', function () {
                                uploadfile(this, element);
                            });
                        });
                    }
                });
            }
            $('body').bind('ajaxComplete', function () {
                setTimeout(function () {
                    if (editor.getElement() && editor.getElement().value === '') {
                        editor.setContent('');
                    }
                }, 200);
            });
        }
    };

    function editorChange(editor)
    {
        setTimeout(function () {
            editor.fire('change');
        }, 500);
    }

    function tinymceInit()
    {
        $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
            var element = this;

            $(element).tinymce(configTinyMCE);
            $(element).on('remove', function () {
                var editor = tinymce.get($(element).attr('id'));
                if (editor) {
                    editor.destroy();
                }
            });
            $(element).addClass('tiny-mce-done');
        });
    }

    function callBack(nodes)
    {
        var nodeId = _.keys(nodes)[0];
        var mimeType = nodes[_.keys(nodes)][2];

        if (mimeType === '') {
            //fix me one day.
            mimeType = 'unknown/mimetype';
        }

        $.ajax(home.path + 'resource/embed/' + nodeId + '/' + mimeType)
        .done(function (data) {
            tinymce.activeEditor.execCommand('mceInsertContent', false, data);
            editorChange(tinymce.activeEditor);
        })
        .error(function () {
            modal.error();
        });
    }

    function resourcePickerOpen()
    {
        if ($('#resourcePicker').get(0) === undefined) {
            $('body').append('<div id="resourcePicker"></div>');
            $.ajax(home.path + 'resource/init')
            .done(function (data) {
                var resourceInit = JSON.parse(data);
                resourceInit.parentElement = $('#resourcePicker');
                resourceInit.isPickerMultiSelectAllowed = false;
                resourceInit.isPickerOnly = true;
                resourceInit.pickerCallback = function (nodes) { callBack(nodes); };
                Claroline.ResourceManager.initialize(resourceInit);
                Claroline.ResourceManager.picker('open');
            })
            .error(function () {
                modal.error();
            });
        } else {
            Claroline.ResourceManager.picker('open');
        }
    }

    function uploadfile(form, modal) {
        var workspace = $(form).data('workspace');
        $(form).upload(
            home.path + 'resource/create/file/' + workspace,
            function (done) {
                if (done.getResponseHeader('Content-Type')  === 'application/json') {
                    var resource = $.parseJSON(done.responseText)[0];
                    var nodes = {};
                    nodes[resource.id] = new Array(resource.name, resource.type, resource.mime_type);
                    $(modal).modal('hide');
                    callBack(nodes);
                } else {
                    $('.progress', modal).addClass('hide');
                    $('.alert', modal).removeClass('hide');
                    $('.progress-bar', modal).attr('aria-valuenow', 0).css('width', '0%').find('sr-only').text('0%');
                }
            },
            function (progress) {
                var percent = Math.round((progress.loaded * 100) / progress.totalSize);

                $('.progress', modal).removeClass('hide');
                $('.alert', modal).addClass('hide');
                $('.progress-bar', modal)
                .attr('aria-valuenow', percent)
                .css('width', percent + '%')
                .find('sr-only').text(percent + '%');
            }
        );
    }

    function toggleFullscreen(element) {
        var modal = $(element).parents('.modal').first();

        if (modal.hasClass('fullscreen')) {
            modal.removeClass('fullscreen');
        } else {
            modal.addClass('fullscreen');
        }
    }

    var domChange;

    $('body').bind('ajaxComplete', function () {
        tinymceInit();
    });

    $('body').on('click', '.mce-widget.mce-btn[aria-label="Fullscreen"]', function () {
        toggleFullscreen(this);
        window.dispatchEvent(new window.Event('resize'));
    });

    $('body').bind('DOMSubtreeModified', function () {
        clearTimeout(domChange);
        domChange = setTimeout(tinymceInit, 10);
    });

    $(document).ready(function () {
        tinymceInit();
    });
}());
