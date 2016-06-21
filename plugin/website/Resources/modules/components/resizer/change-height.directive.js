let _utilities = new WeakMap()
export default class changeHeightDirective {
  constructor (utilityFunctions) {
    _utilities.set(this, utilityFunctions)
    this.restrict = 'A'
  }

  link ($scope, element, attrs) {
    $scope.$watch(
      () => element[ 0 ].scrollHeight,
      (newHeight) => {
        _utilities.get(this).deepSetValue($scope, attrs.changeHeight, newHeight)
      }
    )
  }
}


changeHeightDirective.$inject = ["utilityFunctions"]