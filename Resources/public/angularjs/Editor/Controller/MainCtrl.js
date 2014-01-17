'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $http, $window, $location, $modal, HistoryFactory, ClipboardFactory, PathFactory, StepFactory, AlertFactory) {
    $scope.path = EditorApp.currentPath;
    
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Update History if needed
    if (-1 === HistoryFactory.getHistoryState()) {
        HistoryFactory.update($scope.path);
    }
    
    // Tiny MCE options
    if (typeof(configTinyMCE) != 'undefined' && null != configTinyMCE && configTinyMCE.length != 0) {
        // App as a config for tinyMCE => use it
        $scope.tinymceOptions = configTinyMCE;
    } 
    else {
        var home = window.Claroline.Home;

        var language = home.locale.trim();
        var contentCSS = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';
        
        // If no config, add default tiny
        $scope.tinymceOptions = {
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
            }
        };
    }

    $scope.alerts = AlertFactory.getAlerts();

    /**
     * Copy data into clipboard
     * @returns void
     */
    $scope.copy = function(data) {
        ClipboardFactory.copy(data);
    };

    /**
     * Paste current clipboard content
     * @returns void
     */
    $scope.paste = function(step) {
        ClipboardFactory.paste(step);
        HistoryFactory.update($scope.path);
    };

    /**
     * Undo last user modifications
     * @returns void
     */
    $scope.undo = function() {
        HistoryFactory.undo();
        $scope.path = PathFactory.getPath();
    };

    /**
     * Redo last history modifications
     * @returns void
     */
    $scope.redo = function() {
        HistoryFactory.redo();
        $scope.path = PathFactory.getPath();
    };
}