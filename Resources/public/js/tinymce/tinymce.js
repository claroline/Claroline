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

    var language = home.locale.trim();
    var contentCSS = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';

    var configTinyMCE = {
        relative_urls: false,
        theme: 'modern',
        language: language,
        browser_spellcheck : true,
        autoresize_min_height: 100,
        autoresize_max_height: 500,
        content_css: contentCSS,
        plugins: [
            'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'template paste textcolor emoticons code'
        ],
        toolbar1: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | preview fullscreen resourcePicker',
        toolbar2: 'undo redo | forecolor backcolor emoticons | bullist numlist outdent indent | link image media print code',
        paste_preprocess: function (plugin, args) {
            var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
            var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

            if (url) {
                args.content = '<a href="' + link + '">' + link + '</a>';
                home.generatedContent(link, function (data) {
                    insertContent(data);
                }, false);
            }
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
                    'icon': 'newdocument',
                    'classes': 'widget btn',
                    'tooltip': 'Resources',
                    'onclick': function () {
                        tinymce.activeEditor = editor;
                        resourcePickerOpen();
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
        }, 200);
    }

    function insertContent(content)
    {
        var newNode = tinymce.activeEditor.getDoc().createElement('div');
        newNode.innerHTML = content;
        tinymce.activeEditor.selection.getRng().insertNode(newNode);
        editorChange(tinymce.activeEditor);
    }

    function tinymceInit()
    {
        $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
            $(this).tinymce(configTinyMCE);
            $(this).addClass('tiny-mce-done');
        });
    }

    function callBack(nodes)
    {
        var nodeId = _.keys(nodes)[0];
        var mimeType = nodes[_.keys(nodes)][2];

        $.ajax(home.path + 'resource/embed/' + nodeId + '/' + mimeType)
        .done(function (data) {
            tinymce.activeEditor.execCommand('mceInsertContent', false, data);
            editorChange(tinymce.activeEditor);
        })
        .error(function () {
            home.modal('content/error');
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
                home.modal('content/error');
            });
        } else {
            Claroline.ResourceManager.picker('open');
        }
    }

    var domChange;

    $('body').bind('ajaxComplete', function () {
        tinymceInit();
    });

    $('body').bind('DOMSubtreeModified', function () {
        clearTimeout(domChange);
        domChange = setTimeout(tinymceInit, 10);
    });

    $(document).ready(function () {
        tinymceInit();
    });
}());
