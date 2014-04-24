'use strict';

portfolioApp
    .directive('wiget', function() {
        return {
            scope: {
                type: '=dataType'
            },
            template: '<div class="portfolio_title_actions">' +
                '<button class="portfolio_title_edit_action btn" data-ng-click="editTitle(widget);"><span class="icon-pencil"></span></button>' +
                '</div>' +
                '<div data-ng-bind-html="data.view | trustAsHtml"></div>',
            controller: function($scope) {
                $scope.data = "";
                var init = function() {
//            console.log($scope.widget);
                    if (typeof $scope.widget === "undefined") {
                        $exceptionHandler("The WidgetController must be initialized with a widget in scope");
                    }
                    $scope.data = $scope.widget;
                    // continue iniitialization
                    console.log($scope);
                };

                init();
            },
        };
    });