/**
 * Created by panos on 3/24/16.
 */
let _$scope = new WeakMap()
let _$element = new WeakMap()
export default class Colorpicker {
  constructor ($scope, $element) {
    _$scope.set(this, $scope)
    _$element.set(this, $element)
    this.color = (this.color || 'transparent').toLowerCase()
    //this.colorpicker = $element.colorpicker
    let options = {
      customClass: 'ws-colorpicker',
      format: 'hex',
      color: this.color
    }
    let colorpicker = $element.colorpicker(options);
    colorpicker.on('changeColor.colorpicker', this.onColorpickerChange.bind(this));
  }

  changeColor(colorHex) {
    this.color = colorHex
    _$scope.get(this).$evalAsync()
  }

  clearColor(event) {
    event.preventDefault();
    event.stopPropagation();

    this._setColorpickerColor('transparent');

    return false;
  }

  onColorpickerChange(event) {
    this.changeColor(event.color.toHex());
  }

  _setColorpickerColor(color) {
    _$element.get(this).colorpicker('setValue', color);
  }
}

Colorpicker.$inject = [ '$scope', '$element' ];
