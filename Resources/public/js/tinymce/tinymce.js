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
        //document_base_url: home.path,
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
            'template paste textcolor emoticons'
        ],
        toolbar1: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | preview fullscreen resourcePicker',
        toolbar2: 'undo redo | bullist numlist outdent indent | link image media print | forecolor backcolor emoticons',
        paste_preprocess: function (plugin, args) {
            var url = args.content.match(/href='([^']*')|href="([^"]*")/g);

            if (url) {
                home.generatedContent(url[0].slice(6, -1), function (data) {
                    var newNode = tinymce.activeEditor.getDoc().createElement('div');
                    newNode.innerHTML = data;
                    tinymce.activeEditor.selection.getRng().insertNode(newNode);
                    tinymce.activeEditor.fire('change');
                });
            }
        },
        setup: function (editor) {
            editor.on('change', function () {
                if (editor.getElement()) {
                    editor.getElement().value = editor.getContent();
                }
            });
            editor.on('LoadContent', function () {
                setTimeout(function () {
                    editor.fire('change');
                }, 200);
            });
            if ($(editor.getElement()).data('resource-picker') !== 'off') {
                editor.addButton('resourcePicker', {
                    'icon': 'newdocument',
                    'classes': 'widget btn resource-picker',
                    'tooltip': 'Resources'
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

    function tinymceInit()
    {
        $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
            $(this).tinymce(configTinyMCE);
            $(this).addClass('tiny-mce-done');
        });
    }

    function callBack(nodes)
    {
        var id = _.keys(nodes)[0];
        var resourceTypes = nodes[_.keys(nodes)][1];
        var nodeId = _.keys(nodes)[0];
        var mimeArray = new Array('image/jpeg', 'image/gif', 'image/png');
        var mimeType = nodes[_.keys(nodes)][2];

        if (resourceTypes === 'directory') {
            //the breadcrumbs could be appended to the query string aswell
            var route = Routing.generate(
                'claro_desktop_open_tool', {'toolName': 'resource_manager'}
            ) + '#resources/' + id;
            tinymce.activeEditor.setContent(tinymce.activeEditor.getContent() +
                '<a href="' + route + '">' + nodes[_.keys(nodes)][0] + '</a>'
            );
            tinymce.activeEditor.fire('change');
        } else {
            if (mimeArray.indexOf(mimeType) > -1) {
                tinymce.activeEditor.setContent(tinymce.activeEditor.getContent() +
                    '<img src="' + Routing.generate('claro_file_get_image', {'node': id}) +
                    '" style="max-width:100%"/>'
                );
                tinymce.activeEditor.fire('change');
            } else {
                tinymce.activeEditor.setContent(tinymce.activeEditor.getContent() +
                    '<a href="' +
                    Routing.generate('claro_resource_open', {'resourceType': resourceTypes, 'node' : nodeId}) +
                    '">' + nodes[_.keys(nodes)][0] + '</a>'
                );
                tinymce.activeEditor.fire('change');
            }
        }
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

    $('body').on('click', '.mce-resource-picker', function () {
        resourcePickerOpen();
    });
}());
