'use strict';

commentsApp
    .controller("commentsController", ["$scope", "$timeout", "commentsManager", "assetPath", "tinyMceConfig",
        function($scope, $timeout, commentsManager, assetPath, tinyMceConfig) {
        $scope.comments = commentsManager.comments;
        $scope.tinyMceConfig = tinyMceConfig;
        $scope.assetPath = assetPath;

        $scope.addComment = function(portfolioId, message) {
            commentsManager.addComment(portfolioId, message);
        };
    }]);