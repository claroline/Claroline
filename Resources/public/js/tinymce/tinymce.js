(function () {
    'use strict';

    var home = window.Claroline.Home;
    var content_css = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';
    var language = home.locale.trim();

    var configTinyMCE = {
        selector: 'textarea.claroline-tiny-mce',
        theme: 'modern',
        language: language,
        browser_spellcheck : true,
        autoresize_min_height: 100,
        autoresize_max_height: 500,
        content_css: content_css,
        plugins: [
            'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table directionality',
            'emoticons template paste textcolor'
        ],
        toolbar1: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | preview fullscreen',
        toolbar2: 'undo redo | bullist numlist outdent indent | link image media print | forecolor backcolor emoticons',
        paste_preprocess: function(plugin, args) {
            var url = args.content.match(/href="([^"]*")/g);
            var creator = $(tinymce.activeEditor.getElement()).parents('.creator').get(0);
            if (url && creator) {
                home.generatedContent(creator, url[0].slice(6, -1))
            }

            args.content;
        },
        setup: function(editor) {
            editor.on('change', function() {
                tinymce.activeEditor.getElement().value = tinymce.activeEditor.getContent();
            });
            editor.on('LoadContent', function() {
                setTimeout(function () {
                    tinymce.activeEditor.fire('change');
                }, 10);
            });
        }
    }

    $('body').bind('ajaxComplete', function () {
        tinymce.init(configTinyMCE);
    });

    $(document).ready(function() {
        tinymce.init(configTinyMCE);
    });

}());
