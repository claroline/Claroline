import $ from 'jquery'

let _$document = new WeakMap()

export default class resizerBottom {
  constructor ($document) {
    _$document.set(this, $document)
    this.restrict = 'A'
    this.require = '?resizer'
    this.scope = { resizer: '=' }
  }

  link ($scope, $element, $attrs, resizeController) {
    if (!resizeController) return;

    let $resizeable = null;
    $resizeable = $element[ 0 ].previousElementSibling;
    let maxY = (! $attrs.resizerMax) ? 500 : $attrs.resizerMax
    let minY = (! $attrs.resizerMin) ? 0 : $attrs.resizerMin

    resizeController.$resize = (event) => {
      let y = event.pageY - Math.floor($($resizeable).offset().top);
      if (y <= maxY && y >= minY) {
        $scope.resizer = y;
        $scope.$apply();
        _$document.get(this).find("body").css({cursor: "n-resize"});
      }
    }
  }
}

resizerBottom.$inject = ["$document"]
