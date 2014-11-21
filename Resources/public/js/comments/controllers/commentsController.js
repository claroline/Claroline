'use strict';

commentsApp
    .controller("commentsController", ["$scope", "$timeout", "commentsManager", "assetPath", function($scope, $timeout, commentsManager, assetPath) {
        $scope.comments = commentsManager.comments;
        $scope.tinyMceConfig = {
            forced_root_block : "",
            force_br_newlines : true,
            force_p_newlines : false
        };
        $scope.assetPath = assetPath;

        $scope.addComment = function(portfolioId, message) {
            commentsManager.addComment(portfolioId, message);
        };
    }]);