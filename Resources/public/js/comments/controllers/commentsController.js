'use strict';

commentsApp
    .controller("commentsController", ["$scope", "$timeout", "commentsManager", function($scope, $timeout, commentsManager) {
        $scope.comments = commentsManager.comments;

        $scope.addComment = function(portfolioId, message) {
            commentsManager.addComment(portfolioId, message);
        };
    }]);