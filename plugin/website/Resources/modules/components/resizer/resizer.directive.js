import angular from 'angular/index'

let _$document = new WeakMap()


export default class resizer{
  constructor ($document) {
    _$document.set(this, $document)
    this.restrict = 'A'
  }

  link ($scope, $element, $attrs, ctrl) {
    $element.on('mousedown', (event) => {
      event.preventDefault();
      _$document.get(this).on('mousemove', mousemove.bind(this))
      _$document.get(this).on('mouseup', mouseup.bind(this))
    });
    function mousemove(event) {
      ctrl.$resize(event)
    }

    function mouseup() {
      _$document.get(this).off('mousemove')
      _$document.get(this).off('mouseup')
      _$document.get(this).find("body").css({cursor: "default"})
    }
  }

  controller () {
    this.$resize = angular.noop
  }
}

resizer.$inject = ["$document"]
