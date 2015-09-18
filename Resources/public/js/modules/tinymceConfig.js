'use strict';

var appDirectives = angular.module('app.config', []);

var tinymce = window.tinymce;
tinymce.claroline.init = tinymce.claroline.init || {};
tinymce.claroline.plugins = tinymce.claroline.plugins || {};

var plugins = [
    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars fullscreen',
    'insertdatetime media nonbreaking table directionality',
    'template paste textcolor emoticons code'
];
var toolbar = 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen';

$.each(tinymce.claroline.plugins, function(key, value) {
    if ('autosave' != key &&  value === true) {
        plugins.push(key);
        toolbar += ' ' + key;
    }
});

var tinyMceConfig = {};
for (var prop in tinymce.claroline.configuration) {
    if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
        tinyMceConfig[prop] = tinymce.claroline.configuration[prop];
    }
}

tinyMceConfig.plugins = plugins;
tinyMceConfig.toolbar1 = toolbar;
tinyMceConfig.format = 'text';

appDirectives.value('tinyMceConfig', tinyMceConfig);