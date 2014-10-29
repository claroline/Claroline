'use strict';

portfolioApp
    .controller("commentController", ["$scope", "portfolioManager", "commentsManager", "$timeout",
                              function($scope, portfolioManager, commentsManager, $timeout) {
        $scope.message = "";

        $scope.create = function() {
            if (this.message) {
                var comment = commentsManager.create(portfolioManager.portfolioId, {
                    'message' : this.message
                })
                this.message = '';
            }
        };

        $scope.updateCountViewComments = function () {
            if (0 < portfolioManager.portfolio.unreadComments) {
                portfolioManager.portfolio.commentsViewAt = new Date();
                portfolioManager.save(portfolioManager.portfolio);
            }
            $scope.displayComment= !$scope.displayComment;
        }
    }]);