'use strict';

var appDirectives = angular.module('app.directives', []);

appDirectives
    .directive('scrollContainer', ["commentsManager", "$timeout", function (commentsManager, $timeout) {
        return {
            restrict: "A",
            link: function ($scope, element, attrs) {
                $scope.comments = commentsManager.comments;

                $scope.$watch('comments.length', function(newValue, oldValue) {
                    if (newValue >= oldValue) {
                        $timeout(function(){
                            element[0].scrollTop = element[0].scrollHeight;
                        }, 0);
                    }
                });
            }
        };
    }]);