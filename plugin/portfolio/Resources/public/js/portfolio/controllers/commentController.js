'use strict';

portfolioApp
    .controller("commentController", ["$scope", "portfolioManager", "commentsManager", "$timeout",
                              function($scope, portfolioManager, commentsManager, $timeout) {
        $scope.message = "";

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

        $scope.tinyMceConfig = {};
        for (var prop in tinymce.claroline.configuration) {
            if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
                $scope.tinyMceConfig[prop] = tinymce.claroline.configuration[prop];
            }
        }

        $scope.tinyMceConfig.plugins = plugins;
        $scope.tinyMceConfig.toolbar1 = toolbar;
        $scope.tinyMceConfig.format = 'text';

        $scope.create = function() {
            if (this.message) {
                var comment = commentsManager.create(portfolioManager.portfolioId, {
                    'message' : this.message
                })
                this.message = '';
            }
        };

        $scope.updateCountViewComments = function () {
            $scope.displayComment= !$scope.displayComment;

            if ($scope.displayComment) {
                if (0 < portfolioManager.portfolio.unreadComments) {
                    portfolioManager.updateViewCommentsDate();
                }
            }
        }
    }]);