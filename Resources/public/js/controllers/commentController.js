'use strict';

portfolioApp
    .controller("commentController", ["$scope", "portfolioManager", "commentsManager", "$timeout", function($scope, portfolioManager, commentsManager, $timeout) {
        $scope.displayComment = true;
        $scope.message        = "";

        $scope.create = function() {
            if (this.message) {
                var comment = commentsManager.create(portfolioManager.portfolioId, {
                    'message' : this.message
                })
                this.message = '';
            }
        };
    }]);