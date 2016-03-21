function changeHeightDirective(utilityFunctions) {
  var directive = {
    link: linkFn,
    restrict: 'A'
  };

  return directive;
  /////////

  function linkFn(scope, elem, attrs) {
    scope.$watch(function () {
      return elem[ 0 ].scrollHeight;
    }, function (newHeight) {
      utilityFunctions.deepSetValue(scope, attrs.changeHeight, newHeight);
    });
  }
}

changeHeight.$inject = [ 'utilityFunctions' ]

export default changeHeightDirective