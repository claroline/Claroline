function resizerRightDirective($document) {
  var directive = {
    restrict: 'A',
    require: '?resizer',
    link: linkFn,
    scope: {
      resizer: '='
    }
  };

  return directive;
  /////////

  function linkFn($scope, $element, $attrs, resizeController) {
    if (!resizeController) return;
    var $resizeable = null;
    $resizeable = $element[ 0 ].previousElementSibling;
    var maxX = (!angular.isUndefined($attrs.resizerMax)) ? $attrs.resizerMax : 500;
    var minX = (!angular.isUndefined($attrs.resizerMin)) ? $attrs.resizerMin : 0;

    resizeController.$resize = function (event) {
      var x = event.pageX - Math.floor($($resizeable).offset().left);
      if (x <= maxX && x >= minX) {
        $scope.resizer = x;
        $scope.$apply();
        $document.find("body").css({cursor: "e-resize"});
      }
    }
  }
}

resizerRight.$inject = [ '$document' ]

export default resizerRightDirective
