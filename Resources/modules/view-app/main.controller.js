/**
 * Created by ptsavdar on 15/03/16.
 */
MainController.construct.$inject = ['$scope', 'website.data'];

export default class MainController {
  construct($scope, websiteData) {
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
        basePath: websiteData.path,
        breakpoint: 800
      };
    }
  }
}
