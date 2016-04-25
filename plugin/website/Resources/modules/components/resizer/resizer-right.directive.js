import $ from 'jquery'

let _$document = new WeakMap()

export default class resizerRight {
  constructor ($document) {
    _$document.set(this, $document)
    this.restrict = 'A'
    this.require = '?resizer'
    this.scope = { resizer : '=' }
  }

  link ($scope, $element, $attrs, resizeController) {
    if (!resizeController) return;

    let $resizeable = null;
    $resizeable = $element[ 0 ].previousElementSibling;
    let maxX = (!$attrs.resizerMax) ? 200 : $attrs.resizerMax
    let minX = (!$attrs.resizerMin) ? 50 : $attrs.resizerMin

    resizeController.$resize = (event) => {
      let x = event.pageX - Math.floor($($resizeable).offset().left);
      if (x <= maxX && x >= minX) {
        $scope.resizer = x;
        $scope.$apply();
        _$document.get(this).find("body").css({cursor: "e-resize"});
      }
    }
  }
}

resizerRight.$inject = ["$document"]