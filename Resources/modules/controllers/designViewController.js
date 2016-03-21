(function () {
  angular
    .module('app')
    .controller('designViewController', [ '$location', '$scope', 'website.data', function ($location, $scope, websiteData) {
      $scope.menu = websiteData.pages[ 0 ];
      $scope.options = websiteData.options;
      $scope.contentHeight = 400;
      if (websiteData.options.menuOrientation == 'vertical') {
        $scope.pushMenuOptions = {
          containersToPush: [ $('.website-page-content') ],
          wrapperClass: 'multilevelpushmenu_wrapper',
          menuInactiveClass: 'multilevelpushmenu_inactive',
          menuWidth: $scope.options.menuWidth,
          direction: 'rtl',
          backItemIcon: 'fa fa-angle-left',
          groupIcon: 'fa fa-angle-right',
          backText: 'Back',
          mode: 'cover',
          overlapWidth: 0,
          basePath: websiteData.path
        };

      } else {
        $scope.flexnavOptions = {
          basePath: websiteData.path
        };
        $scope.breakPoint = 800;
      }
    } ]);
})();