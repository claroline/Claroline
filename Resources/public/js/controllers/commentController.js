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
                var wrapper  = $(".communication_panel .media-list")[0];
                this.message = '';

                // will fail if you scroll immediately because we scroll before the view is updated;
                $timeout(function(){
                    wrapper.scrollTop = wrapper.scrollHeight;
                },0);
            }
        };
    }]);