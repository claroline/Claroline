(function() {
  'use strict';

  // Initialize TinyMCE
  var tinymce = window.tinymce;
  tinymce.claroline.init    = tinymce.claroline.init || {};
  tinymce.claroline.plugins = tinymce.claroline.plugins || {};

  var plugins = [
    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars fullscreen',
    'insertdatetime media nonbreaking save table directionality',
    'template paste textcolor emoticons code -accordion -mention -codemirror'
  ];
  var toolbar = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons';

  $.each(tinymce.claroline.plugins, function(key, value) {
    if ('autosave' != key &&  value === true) {
      plugins.push(key);
      toolbar += ' ' + key;
    }
  });

  angular.module('UtilsModule').factory('tinymceConfig', tinymceConfig);

  function tinymceConfig() {
    var config = {};
    for (var prop in tinymce.claroline.configuration) {
      if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
        config[prop] = tinymce.claroline.configuration[prop];
      }
    }

    config.plugins = plugins;
    config.toolbar1 = toolbar;
    config.trusted = true;
    config.format = 'html';

    return config;
  }
})();