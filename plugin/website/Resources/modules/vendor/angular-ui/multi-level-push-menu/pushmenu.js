'use strict'

import $ from 'jquery'
import angular from 'angular/index'

(function () {
  var module

  module = angular.module('wxy.pushmenu', [ 'ngAnimate' ])

  /**
   * Templates
   */

    // Main menu template
  module.run([
    '$templateCache',
    function ($templateCache) {
      $templateCache.put('pushmenu/main_menu.tpl',
        '<div id="menu">' +
        '<nav ng-class="[options.wrapperClass, options.direction]" ng-style="{width: width + \'px\'}">' +
        '<wxy-submenu menu="menu" level="level" visible="visible" menu-style="menuStyle" menu-width="menuWidth"></wxy-submenu>' +
        '</nav>' +
        '</div>'
      )
    }
  ])

  module.run([
    '$templateCache',
    function ($templateCache) {
      $templateCache.put('pushmenu/sub_menu.tpl',
        '<div id="{{menu.id}}" ng-show="visible" ng-style="{width: width + \'px\', \'background-color\': menuStyle.menuBgColor}" ng-class="{visible: visible, multilevelpushmenu_inactive: inactive && !collapsed}" class="levelHolderClass">' +
        '<h2 title="{{menu.title}}">' +
        '<i class="floatRight cursorPointer fa fa-bars" ng-click="openMenu($event, menu)"></i>' +
        '<a ng-click="goBack($event, menu)">' +
        '<i ng-if="level != 0 && !inactive" ng-class="options.backItemIcon"></i>' +
        ' {{menu.title}}' +
        '</a>' +
        '</h2>' +
        '<ul ng-class="{invisible: inactive}">' +
        '<li ng-repeat="item in menu.children" ng-mouseleave="menuLeave(item)" ng-mouseover="menuOver(item)" ng-style="getStyle(item)">' +
        '<i ng-if="item.children && item.children.length>0" ng-click="onSubmenuClicked(item, $event)" class="floatLeft iconSpacing" ng-class="options.groupIcon"></i>' +
        '<a ng-href="{{options.buildHref(item)}}" target="{{!item.target?\'\':\'_blank\'}}" title="{{item.title}}" ng-click="options.onItemClick($event, item)">' +
        '<i ng-if="item.icon" class="floatRight" ng-class="item.icon"></i>' +
        '<span>{{item.title}}</span>' +
        '</a>' +
        '<div ng-if="item.children && item.children.length>0">' +
        '<wxy-submenu menu="item" level="childrenLevel" visible="item.displayed" menu-style="menuStyle" menu-width="menuWidth"></wxy-submenu>' +
        '</div>' +
        '</li>' +
        '</ul>' +
        '</div>'
      )
    }
  ])

  module.directive('wxyPushMenu', [
    'wxyOptions', 'wxyUtils', function (wxyOptions, wxyUtils) {
      return {
        scope: {
          menu: '=',
          options: '=',
          menuWidth: '=',
          menuStyle: '='
        },
        controller: function ($scope, $element) {
          var options, width
          $scope.options = options = angular.extend(wxyOptions, $scope.options)
          $scope.level = 0
          $scope.visible = true
          width = options.menuWidth || 265
          $scope.menuWidth = width
          width = $scope.width = width + options.overlapWidth * wxyUtils.DepthOf($scope.menu)
          this.GetBaseWidth = function () {
            return width
          }
          this.GetOptions = function () {
            return options
          }
          $scope.$watch('menuWidth', function (value) {
            width = parseInt(value)
            $scope.menuWidth = width
            $scope.width = width + options.overlapWidth * wxyUtils.DepthOf($scope.menu)
            wxyUtils.FixLeftContainers(options.containersToPush, width)
          })
        },
        templateUrl: 'pushmenu/main_menu.tpl',
        restrict: 'E',
        replace: true
      }
    }
  ])

  module.directive('wxySubmenu', [
    '$animate', '$compile', 'wxyUtils', function ($animate, $compile, wxyUtils) {
      return {
        scope: {
          menu: '=',
          level: '=',
          visible: '=',
          menuWidth: '=',
          menuStyle: '='
        },
        compile: function compile(tElement, tAttr) {
          var compiledContents, contents
          contents = tElement.contents().remove()
          compiledContents = null
          return {
            pre: function preLink(scope, iElement, iAttr) {
              if (!compiledContents) {
                compiledContents = $compile(contents)
              }
              compiledContents(scope, function (clone) {
                return iElement.append(clone)
              })
            },
            post: function postLink(scope, element, attr, ctrl) {
              var collapse, marginCollapsed, onOpen, options, _this = this

              scope.options = options = ctrl.GetOptions()
              scope.correctionWidth = 0
              scope.childrenLevel = scope.level + 1
              onOpen = function () {
                if (!scope.collapsed) {
                  scope.inactive = false
                }
                scope.$emit('submenuOpened', scope.level)
              }
              if (scope.level === 0) {
                scope.collasped = false
                marginCollapsed = options.rootOverlapWidth - ctrl.GetBaseWidth()
                if (options.collapsed) {
                  scope.collapsed = true
                  scope.inactive = true
                  element.css({
                    marginLeft: marginCollapsed + 'px'
                  })
                }
                collapse = function () {
                  scope.collapsed = !scope.collapsed
                  scope.inactive = scope.collapsed
                  marginCollapsed = options.rootOverlapWidth - ctrl.GetBaseWidth()
                  element.data('from', scope.collapsed ? 0 : marginCollapsed)
                  element.data('to', scope.collapsed ? marginCollapsed : 0)
                  if (scope.collapsed) {
                    options.onCollapseMenuStart()
                  } else {
                    options.onExpandMenuStart()
                  }
                  $animate.addClass(element, 'slide').then(function () {
                    element.removeClass('slide')
                    if (parseInt(element.css('marginLeft')) != element.data('to')) {
                      element.animate({
                        marginLeft: element.data('to') + 'px'
                      })
                    }
                    scope.$apply(function () {
                      if (scope.collapsed) {
                        return options.onCollapseMenuEnd()
                      } else {
                        return options.onExpandMenuEnd()
                      }
                    })
                  })
                  wxyUtils.PushContainers(options.containersToPush, scope.collapsed ? marginCollapsed + ctrl.GetBaseWidth() : ctrl.GetBaseWidth())
                }
              }
              scope.openMenu = function (event, menu) {
                wxyUtils.StopEventPropagation(event)
                scope.$broadcast('menuOpened', scope.level)
                options.onTitleItemClick(event, menu)
                if (scope.level === 0 && !scope.inactive || scope.collapsed) {
                  collapse()
                } else {
                  scope.inactive = !scope.inactive
                  scope.$emit('toggleMenu')
                }
              }
              scope.onSubmenuClicked = function (item) {
                if (item.children && item.children.length > 0) {
                  item.displayed = true
                  scope.inactive = true
                  //options.onGroupItemClick($event, item);
                }
              }
              scope.goBack = function (event, menu) {
                if (scope.level != 0) {
                  options.onBackItemClick(event, menu)
                  scope.visible = false
                  return scope.$emit('submenuClosed', scope.level)
                }
              }
              scope.$watch('visible', function (visible) {
                if (visible) {
                  if (scope.level > 0) {
                    options.onExpandMenuStart()
                    var fromAnimation = 0
                    var toAnimation = 0
                    if (options.direction == 'ltr') {
                      fromAnimation = -ctrl.GetBaseWidth()
                    } else if (options.direction == 'rtl') {
                      fromAnimation = ctrl.GetBaseWidth()
                    }
                    element.data('from', fromAnimation)
                    element.data('to', toAnimation)
                    $animate.addClass(element, 'slide').then(function () {
                      element.removeClass('slide')
                      scope.$apply(function () {
                        options.onExpandMenuEnd()
                      })
                    })
                  }
                  onOpen()
                }
              })
              scope.$watch('menuWidth', function () {
                scope.width = ctrl.GetBaseWidth() + scope.correctionWidth
              })
              scope.$on('submenuOpened', function (event, level) {
                var correction
                correction = level - scope.level
                scope.correctionWidth = options.overlapWidth * correction
                scope.width = ctrl.GetBaseWidth() + scope.correctionWidth
                if (scope.level === 0) {
                  wxyUtils.PushContainers(options.containersToPush, scope.width)
                }
              })
              scope.$on('submenuClosed', function (event, level) {
                if (level - scope.level === 1) {
                  onOpen()
                  wxyUtils.StopEventPropagation(event)
                }
              })
              scope.$on('menuOpened', function (event, level) {
                if (scope.level - level > 0) {
                  scope.visible = false
                }
              })
              scope.$on('toggleMenu', function (event) {

                if (scope.level === 0) {
                  wxyUtils.StopEventPropagation(event)
                  collapse()
                }
              })
              scope.getStyle = function (item) {
                return wxyUtils.getStyle(scope.menuStyle, item, item.hovered)
              }
              scope.menuOver = function (item) {
                item.hovered = true
              }
              scope.menuLeave = function (item) {
                item.hovered = false
              }
            }
          }
        },
        templateUrl: 'pushmenu/sub_menu.tpl',
        require: '^wxyPushMenu',
        restrict: 'EA',
        replace: true
      }
    }
  ])

  module.factory('wxyUtils', function () {
    var DepthOf, PushContainers, StopEventPropagation, FixLeftContainers, getStyle
    StopEventPropagation = function (e) {
      if (e.stopPropagation && e.preventDefault) {
        e.stopPropagation()
        e.preventDefault()
      } else {
        e.cancelBubble = true
        e.returnValue = false
      }
    }
    DepthOf = function (menu) {
      var depth, item, maxDepth, _i, _len, _ref
      maxDepth = 0
      if (menu.children) {
        _ref = menu.children
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          item = _ref[ _i ]
          if (item.children && item.children.length > 0) {
            depth = DepthOf(item) + 1
          }
          if (depth > maxDepth) {
            maxDepth = depth
          }
        }
      }
      return maxDepth
    }
    PushContainers = function (containersToPush, absoluteDistance) {
      if (!containersToPush) {
        return
      }
      return $.each(containersToPush, function () {
        return $(this).stop().animate({
          marginLeft: absoluteDistance
        })
      })
    }
    FixLeftContainers = function (containersToPush, leftValue) {
      if (!containersToPush) {
        return
      }
      return $.each(containersToPush, function () {
        return $(this).css({marginLeft: leftValue + 'px'})
      })
    }
    getStyle = function (navStyle, menu, hovered) {
      var style = {
        color: (menu.isSection ? navStyle.sectionFontColor : navStyle.menuFontColor),
        'border-color': navStyle.menuBorderColor,
        'background-color': hovered ? navStyle.menuHoverColor : (menu.isSection ? navStyle.sectionBgColor : navStyle.menuBgColor),
        'font-weight': navStyle.menuFontWeight,
        'font-family': navStyle.menuFontFamily,
        'font-size': navStyle.menuFontSize,
        'font-style': navStyle.menuFontStyle
      }
      return style
    }

    return {
      StopEventPropagation: StopEventPropagation,
      DepthOf: DepthOf,
      PushContainers: PushContainers,
      FixLeftContainers: FixLeftContainers,
      getStyle: getStyle
    }
  })

  module.animation('.slide', function () {
    return {
      addClass: function (element) {
        element.removeClass('slide')
        element.css({
          marginLeft: element.data('from') + 'px'
        })
        element.animate({
          marginLeft: element.data('to') + 'px'
        })
      }
    }
  })

  module.value('wxyOptions', {
    containersToPush: null,
    wrapperClass: 'multilevelpushmenu_wrapper',
    menuInactiveClass: 'multilevelpushmenu_inactive',
    menuWidth: 265,
    menuHeight: 0,
    collapsed: false,
    fullCollapse: true,
    direction: 'ltr',
    backText: 'Back',
    backItemClass: 'backItemClass',
    backItemIcon: 'fa fa-angle-right',
    groupIcon: 'fa fa-angle-left',
    mode: 'overlap',
    overlapWidth: 40,
    rootOverlapWidth: 40,
    preventItemClick: true,
    preventGroupItemClick: true,
    swipe: 'both',
    buildHref: function () {

    },
    onCollapseMenuStart: function () {
    },
    onCollapseMenuEnd: function () {
    },
    onExpandMenuStart: function () {
    },
    onExpandMenuEnd: function () {
    },
    onGroupItemClick: function () {
    },
    onItemClick: function () {
    },
    onTitleItemClick: function () {
    },
    onBackItemClick: function () {
    },
    onMenuReady: function () {
    }
  })

}).call(this)
