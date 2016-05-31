import 'angular/angular.min'

angular.module('ui.asset', [])
  .filter('asset', () => {
    return (name) => {
      const basePath = angular.element('#baseAsset').html()
      return basePath + name
    }
  })
