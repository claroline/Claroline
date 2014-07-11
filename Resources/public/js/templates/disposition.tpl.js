'use strict';

angular.module('portfolioApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('templates/disposition.tpl.html',
            '<div class="popover" id="disposition_popover">' +
                '<div class="arrow"></div>' +
                '<div class="popover-content">' +
                    '<ul class="dispositions">' +
                        '<li class="disposition" data-ng-class="{\'selected\': portfolio.disposition == 0, \'idle\': !changing, \'loading\': changing}" data-ng-click="changeDisposition(0)">' +
                        '<div>' +
                            '<div class="col-md-12 border">1</div>' +
                        '</div>' +
                        '</li>' +
                    '<li class="disposition" data-ng-class="{\'selected\': portfolio.disposition == 1, \'idle\': !changing, \'loading\': changing}" data-ng-click="changeDisposition(1)">' +
                        '<div>' +
                            '<div class="col-md-6 border">1</div>' +
                            '<div class="col-md-6 border">2</div>' +
                        '</div>' +
                        '</li>' +
                    '<li class="disposition" data-ng-class="{\'selected\': portfolio.disposition == 2, \'idle\': !changing, \'loading\': changing}" data-ng-click="changeDisposition(2)">' +
                        '<div>' +
                            '<div class="col-md-4 border">1</div>' +
                            '<div class="col-md-4 border">2</div>' +
                            '<div class="col-md-4 border">3</div>' +
                        '</div>' +
                        '</li>' +
                    '</ul>' +
                '</div>' +
            '</div>');
    }
]);