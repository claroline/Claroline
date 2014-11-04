websiteApp.controller('designViewController', ['$location', '$scope', '$alert', function($location, $scope, $alert){
    $scope.menu = window.pages[0];
    $scope.options = window.websiteOptions;
    $scope.contentHeight = 400;
    if (window.websiteOptions.menuOrientation == 'vertical') {
        $scope.pushMenuOptions = {
            containersToPush: [$('.website-page-content')],
            wrapperClass: 'multilevelpushmenu_wrapper',
            menuInactiveClass: 'multilevelpushmenu_inactive',
            menuWidth: $scope.options.menuWidth,
            direction: 'rtl',
            backItemIcon: 'fa fa-angle-left',
            groupIcon: 'fa fa-angle-right',
            backText: 'Back',
            mode: 'cover',
            overlapWidth: 0,
            basePath: window.websitePath
        };

    } else {
        $scope.flexnavOptions = {
            basePath: window.websitePath
        };
        $scope.breakPoint = 800;
    }
}]);