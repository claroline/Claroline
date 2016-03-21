function resizerBottomDirective($document) {
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
    var maxY = (!angular.isUndefined($attrs.resizerMax)) ? $attrs.resizerMax : 500;
    var minY = (!angular.isUndefined($attrs.resizerMin)) ? $attrs.resizerMin : 0;

    resizeController.$resize = function (event) {
      var y = event.pageY - Math.floor($($resizeable).offset().top);
      if (y <= maxY && y >= minY) {
        $scope.resizer = y;
        $scope.$apply();
        $document.find("body").css({cursor: "n-resize"});
      }
    }
  }
}

resizerBottomDirective.$inject = [ '$document' ]

export default resizerBottomDirective