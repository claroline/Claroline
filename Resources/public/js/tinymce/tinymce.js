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
    var routing =  window.Routing;

    //Load external plugins
    tinymce.PluginManager.load('mention', home.asset + 'bundles/frontend/tinymce/plugins/mention/plugin.min.js');
    tinymce.PluginManager.load('accordion', home.asset + 'bundles/frontend/tinymce/plugins/accordion/plugin.min.js');
    tinymce.DOM.loadCSS(home.asset + 'bundles/frontend/tinymce/plugins/mention/css/autocomplete.css');

    /**
     * Claroline TinyMCE parameters and methods.
     */
    tinymce.claroline = {
        'disableBeforeUnload': false,
        'domChange': null
    };

    /**
     * This method fix the height of TinyMCE after modify it,
     * this is usefull when change manually something in the editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.editorChange = function (editor)
    {
        setTimeout(function () {
            var container = $(editor.getContainer()).find('iframe').first();
            var height = container.contents().height();
            var max = 'autoresize_max_height';
            var min = 'autoresize_min_height';

            switch (true)
            {
                case (height <= tinymce.claroline.configuration[min]):
                    container.css('height', tinymce.claroline.configuration[min]);
                    break;
                case (height >= tinymce.claroline.configuration[max]):
                    container.css('height', tinymce.claroline.configuration[max]);
                    container.css('overflow', 'scroll');
                    break;
                default:
                    container.css('height', height);
            }
        }, 500);
    };

    /**
     * This method if fired when paste in a TinyMCE editor.
     *
     *  @param plugin TinyMCE paste plugin object.
     *  @param args TinyMCE paste plugin arguments.
     *
     */
    tinymce.claroline.paste = function (plugin, args)
    {
        var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery

        home.canGenerateContent(link, function (data) {
            tinymce.activeEditor.insertContent('<div>' + data + '</div>');
            tinymce.claroline.editorChange(tinymce.activeEditor);
        });
    };

    /**
     * Check if a TinyMCE editor has change.
     *
     * @return boolean.
     *
     */
    tinymce.claroline.checkBeforeUnload = function ()
    {
        if (!tinymce.claroline.disableBeforeUnload) {
            for (var id in tinymce.editors) {
                if (tinymce.editors.hasOwnProperty(id) &&
                    tinymce.editors[id].isBeforeUnloadActive &&
                    tinymce.editors[id].getContent() !== '' &&
                    $(tinymce.editors[id].getElement()).data('saved')
                    ) {
                    return true;
                }
            }
        }

        return false;
    };

    /**
     * Set the edition detection parameter for a TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.setBeforeUnloadActive = function (editor)
    {
        if ($(editor.getElement()).data('before-unload') !== 'off') {
            editor.isBeforeUnloadActive = true;
        } else {
            editor.isBeforeUnloadActive = false;
        }
    };

    /**
     * Add or remove fullscreen class name in a modal containing a TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.toggleFullscreen = function (element)
    {
        $(element).parents('.modal').first().toggleClass('fullscreen');
    };

    /**
     * This method is fired when a resource is addesd to a TinyMCE editor (through a ResourcePicker).
     *
     * @param nodes A node reource in json.
     *
     */
    tinymce.claroline.callBack = function (nodes)
    {
        var nodeId = _.keys(nodes)[0];
        var mimeType = nodes[_.keys(nodes)][2] !== '' ? nodes[_.keys(nodes)][2] : 'unknown/mimetype';

        $.ajax(home.path + 'resource/embed/' + nodeId + '/' + mimeType)
            .done(function (data) {
                tinymce.activeEditor.insertContent(data);
                tinymce.claroline.editorChange(tinymce.activeEditor);
            })
            .error(function () {
                modal.error();
            });
    };

    /**
     * Upload a file and add it in a TinyMCE editor.
     *
     * @param form A HTML form element.
     * @param element A HTML modal element.
     *
     */
    tinymce.claroline.uploadfile = function (form, element) {
        var workspace = $(form).data('workspace');
        $(form).upload(
            home.path + 'resource/create/file/' + workspace,
            function (done) {
                if (done.getResponseHeader('Content-Type')  === 'application/json') {
                    var resource = $.parseJSON(done.responseText)[0];
                    var nodes = {};
                    var mimeType = 'mime_type'; //camel case fix in order to have 0 jshint errors
                    nodes[resource.id] = new Array(resource.name, resource.type, resource[mimeType]);
                    $(element).modal('hide');
                    tinymce.claroline.callBack(nodes);
                    $.ajax(
                        routing.generate('claro_resource_open_perms', {'node': resource.id})
                    );
                } else {
                    $('.progress', element).addClass('hide');
                    $('.alert', element).removeClass('hide');
                    $('.progress-bar', element).attr('aria-valuenow', 0).css('width', '0%').find('sr-only').text('0%');
                }
            },
            function (progress) {
                var percent = Math.round((progress.loaded * 100) / progress.totalSize);

                $('.progress', element).removeClass('hide');
                $('.alert', element).addClass('hide');
                $('.progress-bar', element)
                    .attr('aria-valuenow', percent)
                    .css('width', percent + '%')
                    .find('sr-only').text(percent + '%');
            }
        );
    };

    /**
     * Open a resource picker from a TinyMCE editor.
     *
     */
    tinymce.claroline.resourcePickerOpen = function ()
    {
        if ($('#resourcePicker').get(0) === undefined) {
            $('body').append('<div id="resourcePicker"></div>');
            $.ajax(home.path + 'resource/init')
                .done(function (data) {
                    var resourceInit = JSON.parse(data);
                    resourceInit.parentElement = $('#resourcePicker');
                    resourceInit.isPickerMultiSelectAllowed = false;
                    resourceInit.isPickerOnly = true;
                    resourceInit.pickerCallback = function (nodes) { tinymce.claroline.callBack(nodes); };
                    Claroline.ResourceManager.initialize(resourceInit);
                    Claroline.ResourceManager.picker('open');
                })
                .error(function () {
                    modal.error();
                });
        } else {
            Claroline.ResourceManager.picker('open');
        }
    };

    /**
     * Add resource picker and upload files buttons in a TinyMCE editor if data-resource-picker is on.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.addResourcePicker = function (editor)
    {
        if ($(editor.getElement()).data('resource-picker') !== 'off') {
            editor.addButton('resourcePicker', {
                'icon': 'none icon-folder-open',
                'classes': 'widget btn',
                'tooltip': translator.get('platform:resources'),
                'onclick': function () {
                    tinymce.activeEditor = editor;
                    tinymce.claroline.resourcePickerOpen();
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
                            tinymce.claroline.resourcePickerOpen();
                        })
                            .on('click', '.filePicker', function () {
                                $('#file_form_file').click();
                            })
                            .on('change', '#file_form_file', function () {
                                tinymce.claroline.uploadfile(this, element);
                            });
                    });
                }
            });
        }
    };

    /**
     * Setup configuration of TinyMCE editor.
     *
     * @param editor A TinyMCE editor object.
     *
     */
    tinymce.claroline.setup = function (editor)
    {
        editor.on('change', function () {
            if (editor.getElement()) {
                editor.getElement().value = editor.getContent();
                if (editor.isBeforeUnloadActive) {
                    $(editor.getElement()).data('saved', 'false');
                    tinymce.claroline.disableBeforeUnload = false;
                }
            }
        }).on('LoadContent', function () {
            tinymce.claroline.editorChange(editor);
        });

        editor.on('BeforeRenderUI', function () {
            editor.theme.panel.find('toolbar').slice(1).hide();
        });

        // Add a button that toggles toolbar 1+ on/off
        editor.addButton('displayAllButtons', {
            'icon': 'none icon-chevron-down',
            'classes': 'widget btn',
            'tooltip': translator.get('platform:tinymce_all_buttons'),
            onclick: function () {
                if (!this.active()) {
                    this.active(true);
                    editor.theme.panel.find('toolbar').slice(1).show();
                } else {
                    this.active(false);
                    editor.theme.panel.find('toolbar').slice(1).hide();
                }
            }
        });

        tinymce.claroline.addResourcePicker(editor);
        tinymce.claroline.setBeforeUnloadActive(editor);
        $('body').bind('ajaxComplete', function () {
            setTimeout(function () {
                if (editor.getElement() && editor.getElement().value === '') {
                    editor.setContent('');
                }
            }, 200);
        });
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsSource = function (query, process, delimiter)
    {
        if (!_.isUndefined(window.Workspace) && !_.isNull(window.Workspace.id)) {
            if (delimiter === '@' && query.length > 0) {
                var searchUserInWorkspaceUrl = routing.generate('claro_user_search_in_workspace') + '/';

                $.getJSON(searchUserInWorkspaceUrl + window.Workspace.id + '/' + query, function (data) {
                    if (!_.isEmpty(data) && !_.isUndefined(data.users) && !_.isEmpty(data.users)) {
                        process(data.users);
                    }
                });
            }
        }
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsItem = function (item)
    {
        var avatar = '<i class="icon-user"></i>';
        if (item.avatar !== null) {
            avatar = '<img src="' + home.asset + 'uploads/pictures/' + item.avatar + '" alt="' + item.name +
                     '" class="img-responsive">';
        }

        return '<li>' +
            '<a href="javascript:;"><span class="user-picker-dropdown-avatar">' + avatar + '</span>' +
            '<span class="user-picker-dropdown-name">' + item.name + '</span>' +
            '<small class="user-picker-avatar-mail text-muted">(' + item.mail + ')</small></a>' +
            '</li>';
    };

    /**
     * @todo documentation
     */
    tinymce.claroline.mentionsInsert = function (item)
    {
        var publicProfileUrl = routing.generate('claro_public_profile_view') + '/';

        return '<user id="' + item.id + '"><a href="' + publicProfileUrl + item.id + '">' + item.name + '</a></user>';
    };

    /**
     * Configuration and parameters of a TinyMCE editor.
     */
    tinymce.claroline.configuration = {
        'relative_urls': false,
        'theme': 'modern',
        'language': home.locale.trim(),
        'browser_spellcheck': true,
        'autoresize_min_height': 100,
        'autoresize_max_height': 500,
        'content_css': home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css',
        'plugins': [
            'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'template paste textcolor emoticons code -mention -accordion'
        ],
        'toolbar1': 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | ' +
                    'resourcePicker fileUpload | fullscreen displayAllButtons',
        'toolbar2': 'styleselect | undo redo | forecolor backcolor | bullist numlist | outdent indent | ' +
                    'image media link charmap | print preview code',
        'extended_valid_elements': 'user[id], a[data-toggle|data-parent]',
        'paste_preprocess': tinymce.claroline.paste,
        'setup': tinymce.claroline.setup,
        'mentions': {
            'source': tinymce.claroline.mentionsSource,
            'render': tinymce.claroline.mentionsRender,
            'insert': tinymce.claroline.mentionsInsert,
            'delay': 200
        }
    };

    /**
     * Initialization function for TinyMCE editors.
     */
    tinymce.claroline.initialization = function ()
    {
        $('textarea.claroline-tiny-mce:not(.tiny-mce-done)').each(function () {
            var element = this;

            $(element).tinymce(tinymce.claroline.configuration)
                .on('remove', function () {
                    var editor = tinymce.get($(element).attr('id'));
                    if (editor) {
                        editor.destroy();
                    }
                })
                .addClass('tiny-mce-done');
        });
    };

    /** Events **/

    $('body').bind('ajaxComplete', function () {
        tinymce.claroline.initialization();
    })
    .on('click', '.mce-widget.mce-btn[aria-label="Fullscreen"]', function () {
        tinymce.claroline.toggleFullscreen(this);
        $(window).scrollTop($(this).parents('.mce-tinymce.mce-container.mce-panel').first().offset().top);
        window.dispatchEvent(new window.Event('resize'));
    })
    .bind('DOMSubtreeModified', function () {
        clearTimeout(tinymce.claroline.domChange);
        tinymce.claroline.domChange = setTimeout(tinymce.claroline.initialization, 10);
    })
    .on('click', 'form *[type=submit]', function () {
        tinymce.claroline.disableBeforeUnload = true;
    });

    $(document).ready(function () {
        tinymce.claroline.initialization();
    });

    $(window).on('beforeunload', function () {
        if (tinymce.claroline.checkBeforeUnload()) {
            return translator.get('platform:leave_this_page');
        }
    });
}());
