(function () {
  'use strict';

  angular
    .module('ui.flexnav')
    .directive('uiFlexnav', uiFlexnav);

  function uiFlexnav() {
    var directive = {
      scope: {
        menu: '=',
        options: '=',
        navStyle: '='
      },
      templateUrl: 'flexnav/main_menu.tpl',
      restrict: 'E',
      replace: true,
      controller: uiFlexnavController,
      controllerAs: 'vm'
    }

    uiFlexnavController.$inject = [ 'flexnav.options', 'flexnav.utils', '$window', '$document', '$scope', '$element' ];

    return directive;
    ////////////////////////////////

    function uiFlexnavController(flexnavOptions, flexnavUtils, $window, $document, $scope, $element) {
      var vm = this;
      vm.menu = $scope.menu;
      vm.navStyle = $scope.navStyle;
      vm.breakpoint = parseInt($scope.options.breakpoint);
      vm.windowElt = angular.element($window);
      vm.lgScreen = vm.windowElt.width() >= vm.breakpoint;
      vm.navWidth = (vm.lgScreen == true) ? (100 / vm.menu.children.length + '%') : '100%';
      vm.mobileOpen = false;
      vm.options = angular.extend(flexnavOptions, $scope.options);
      vm.level = 0;
      vm.hovered = false;
      vm.getOptions = getOptions;
      vm.getStyle = getStyle;
      vm.menuOver = menuOver;
      vm.menuLeave = menuLeave;
      vm.getWindowWidth = getWindowWidth;
      vm.toggleMobileMenu = toggleMobileMenu;

      init();
      ///////////////////////////

      function init() {
        $scope.$watch(vm.getWindowWidth, function () {
          vm.lgScreen = vm.windowElt.width() >= vm.breakpoint;
          vm.navWidth = (vm.lgScreen == true) ? (100 / vm.menu.children.length + '%') : '100%';
        });

        vm.windowElt.bind('resize', function () {
          $scope.$apply();
        });

        $document.on('click', function (event) {
          var isDocumentClicked = $element.find(event.target).length <= 0;
          if (isDocumentClicked) {
            for (var i = 0; i < vm.menu.children.length; i++) {
              vm.menu.children[ i ].show = false;
            }
            if (!vm.lgScreen) {
              vm.mobileOpen = false;
            }
            $scope.$apply();
          }
        });
      }

      function getWindowWidth() {
        return vm.windowElt.width();
      };
      function toggleMobileMenu() {
        vm.mobileOpen = !vm.mobileOpen;
      };
      function getOptions() {
        return vm.options;
      };
      function getStyle() {
        return flexnavUtils.getStyle(vm.navStyle, vm.menu, vm.hovered);
      };
      function menuOver() {
        vm.hovered = true;
      };
      function menuLeave() {
        vm.hovered = false;
      };
    };
  };
})();