'use strict';

commentsApp
    .controller("commentsController", ["$scope", "$timeout", "commentsManager", function($scope, $timeout, commentsManager) {
        $scope.comments = commentsManager.comments;
        $scope.tinyMceConfig = {
            forced_root_block : "",
            force_br_newlines : true,
            force_p_newlines : false
        };

        $scope.addComment = function(portfolioId, message) {
            commentsManager.addComment(portfolioId, message);
        };
    }]);