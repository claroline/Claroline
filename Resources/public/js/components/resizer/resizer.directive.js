(function(){
    'use strict';

    angular
        .module('ui.resizer')
        .directive('resizer', resizer);

    resizer.$inject = ['$document'];
    function resizer($document) {
        var directive = {
            controller: resizeController,
            link: linkFn,
            restrict: 'A'
        };

        return directive;
        /////////

        function linkFn($scope, $element, $attrs, ctrl) {
            $element.on('mousedown', function(event) {
                event.preventDefault();
                $document.on('mousemove', mousemove);
                $document.on('mouseup', mouseup);
            });
            function mousemove(event) {
                ctrl.$resize(event);
            }
            function mouseup() {
                $document.unbind('mousemove', mousemove);
                $document.unbind('mouseup', mouseup);
                $document.find("body").css({cursor:"default"});
            }
        }

        function resizeController() {
            this.$resize = angular.noop;
        }
    }
})();