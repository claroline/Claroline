(function() {
    'use strict';

    var appDirectives = angular.module('app.config', []);

    var tinymce = window.tinymce;
    tinymce.claroline.init = tinymce.claroline.init || {};
    tinymce.claroline.plugins = tinymce.claroline.plugins || {};

    var plugins = [
        'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars fullscreen',
        'insertdatetime media nonbreaking save table directionality',
        'template paste textcolor emoticons code -mention -accordion -codemirror'
    ];
    var toolbar1 = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons';
    $.each(tinymce.claroline.plugins, function(key, value) {
        if ('autosave' != key &&  value === true) {
            plugins.push(key);
            toolbar1 += ' ' + key;
        }
    });

    var tinyMceConfig = {};
    for (var prop in tinymce.claroline.configuration) {
        if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
            tinyMceConfig[prop] = tinymce.claroline.configuration[prop];
        }
    }

    tinyMceConfig.plugins = plugins;
    tinyMceConfig.toolbar1 = toolbar1;
    tinyMceConfig.format = 'text';

    appDirectives.value('tinyMceConfig', tinyMceConfig);
})();