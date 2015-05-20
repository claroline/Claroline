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
    var resourceManager = window.Claroline.ResourceManager;
    var translator = window.Translator;
    var buttons = window.tinymce.claroline.buttons || {};

    tinymce.claroline.buttons.resource_picker = function(editor){
        editor.addButton('resourcePicker', {
            'icon': 'none fa fa-folder-open',
            'classes': 'widget btn',
            'tooltip': translator.trans('resources', {}, 'platform'),
            'onclick': function () {
                tinymce.activeEditor = editor;
                tinymce.claroline.buttons.resourcePickerOpen();
            }
        });
    };
}());
