(function () {
  'use strict';

  angular
    .module('ui.flexnav')
    .directive('uiFlexnavSubmenu', uiFlexnavSubmenu);
  uiFlexnavSubmenu.$inject = [ '$compile', 'flexnav.utils' ];
  function uiFlexnavSubmenu($compile, flexnavUtils) {
    var directive = {
      scope: {
        menu: '=',
        level: '=',
        lgScreen: '=',
        siblings: '=',
        navWidth: '=',
        navStyle: '='
      },
      compile: compile,
      templateUrl: 'flexnav/sub_menu.tpl',
      require: '^uiFlexnav',
      restrict: 'EA',
      replace: true
    }

    return directive;
    ////////////////////////////////

    function compile(tElement) {
      var compiledContents, contents;

      var compile = {
        pre: preLink,
        post: postLink
      };

      init();

      return compile;
      ///////////////////////////

      function init() {
        contents = tElement.contents().remove();
        compiledContents = null;
      }

      function preLink(scope, iElement) {
        if (!compiledContents) {
          compiledContents = $compile(contents);
        }
        compiledContents(scope, function (clone) {
          return iElement.append(clone);
        });
      }

      function postLink(scope, element, attr, ctrl) {
        var watchDisabled = true;

        scope.menu.show = false;
        scope.collapsed = true;
        scope.hovered = false;
        scope.options = ctrl.getOptions();
        scope.correctionWidth = 0;
        scope.childrenLevel = scope.level + 1;
        scope.onSubmenuClicked = function (item) {
          item.show = !item.show;
          if (scope.lgScreen) {
            for (var i = 0; i < scope.siblings.length; i++) {
              if (scope.siblings[ i ] != item) {
                scope.siblings[ i ].show = false;
              }
            }
          }
        };

        scope.$watch('menu.show', function (newValue) {
          if (!watchDisabled) {
            if (newValue == true) {
              scope.$emit('submenuShown');
            } else {
              scope.$broadcast('submenuHidden');
            }
          }
          watchDisabled = false;
        });
        scope.$on('submenuShown', function () {
          scope.menu.show = true;
          scope.collapsed = false;
        });
        scope.$on('submenuHidden', function () {
          scope.menu.show = false;
          scope.collapsed = true;
        });
        scope.getStyle = function () {
          return flexnavUtils.getStyle(scope.navStyle, scope.menu, scope.hovered);
        };
        scope.submenuOver = function () {
          scope.hovered = true;
        };
        scope.submenuLeave = function () {
          scope.hovered = false;
        };
      }
    }
  };
})();