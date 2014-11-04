resizerApp.directive('resizer', ['$document', function ($document) {
    return {
        controller: 'resizeController',
        link: linkFn
    };

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
}]).directive('resizerRight', ['$document', 'UtilityFunctions', function($document, UtilityFunctions){
    return{
        restrict: 'A',
        require: '?resizer',
        link:linkFn
    };

    function linkFn($scope, $element, $attrs, resizeController) {
        if(!resizeController) return;

        var $resizeable = null;
        $resizeable = $element[0].previousElementSibling;
        var maxX = (!angular.isUndefined($attrs.resizerMax))?$attrs.resizerMax:500;
        var minX = (!angular.isUndefined($attrs.resizerMin))?$attrs.resizerMin:0;

        resizeController.$resize = function(event) {
            var x = event.pageX - $resizeable.offsetLeft;
            if(x<=maxX&&x>=minX){
                /*angular.element($resizeable).css({
                 width: x + 'px'
                 });*/
                UtilityFunctions.deepSetValue($scope, $attrs.resizer, x);
                $scope.$apply();
                $document.find("body").css({cursor:"e-resize"});
            }
        }
    }

}]).directive('resizerBottom', ['$document', 'UtilityFunctions', function($document, UtilityFunctions){
    return{
        restrict: 'A',
        require: '?resizer',
        link:linkFn
    };

    function linkFn($scope, $element, $attrs, resizeController) {
        if(!resizeController) return;

        var $resizeable = null;
        $resizeable = $element[0].previousElementSibling;
        var maxY = (!angular.isUndefined($attrs.resizerMax))?$attrs.resizerMax:500;
        var minY = (!angular.isUndefined($attrs.resizerMin))?$attrs.resizerMin:0;

        resizeController.$resize = function(event) {
            var y = event.pageY - $resizeable.offsetTop;
            if(y<=maxY&&y>=minY){
                /*angular.element($resizeable).css({
                 height: y + 'px'
                 });*/
                UtilityFunctions.deepSetValue($scope, $attrs.resizer, y);
                $scope.$apply();
                $document.find("body").css({cursor:"n-resize"});
            }
        }
    }

}]);

resizerApp.directive( 'changeHeight', ['UtilityFunctions', function(UtilityFunctions) {
    return {
        link: function( scope, elem, attrs ) {
            scope.$watch( function () {
                return elem[0].scrollHeight;
            }, function( newHeight, oldHeight ) {
                UtilityFunctions.deepSetValue(scope, attrs.changeHeight, newHeight);
            } );
        }
    }
}]).directive( 'changeWidth', ['UtilityFunctions', function(UtilityFunctions) {
    return {
        link: function( scope, elem, attrs ) {
            scope.$watch( function () {
                return elem[0].scrollWidth;
            }, function( newWidth, oldWidth ) {
                UtilityFunctions.deepSetValue(scope, attrs.changeWidth, newWidth);
            } );
        }
    }
}]);