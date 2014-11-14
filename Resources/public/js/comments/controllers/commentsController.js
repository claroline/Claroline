'use strict';

commentsApp
    .controller("commentsController", ["$scope", "$timeout", "commentsManager", function($scope, $timeout, commentsManager) {
        $scope.comments = commentsManager.comments;
    }]);