'use strict';

(function() {
    var module;

    module = angular.module('ui.flexnav', []);

    /**
     * Templates
     */

        // Main menu template
    module.run([
        '$templateCache',
        function($templateCache) {
            $templateCache.put('flexnav/main_menu.tpl',
                '<div class="flexnav-menu" ng-style="{width:totalWidth}" >'+
                    '<div ng-class="options.buttonClass" ng-style="getStyle()" ng-mouseover="menuOver()" ng-mouseleave="menuLeave()">'+
                    '<span>{{menu.title}}</span>'+
                    '<span ng-if="menu.children && menu.children.length>0" class="touch-button" ng-click="toggleMobileMenu()">'+
                    '<i class="fa fa-bars"></i>'+
                    '</span>'+
                    '</div>'+
                    '<nav>'+
                    '<ul class="flexnav with-js" ng-class="{\'lg-screen\':lgScreen, \'sm-screen\':!lgScreen, \'flexnav-show\':mobileOpen}">'+
                    '<flexnav-submenu ng-repeat="item in menu.children" menu="item" level="level" nav-style="navStyle" siblings="menu.children" nav-width="navWidth" lg-screen=\'lgScreen\'></flexnav-submenu>'+
                    '</ul>'+
                    '</nav>'+
                    '<div style="clear: both;"></div>'+
                    '</div>'
            )
        }
    ]);

    module.run([
        '$templateCache',
        function($templateCache) {
            $templateCache.put('flexnav/sub_menu.tpl',
                '<li id="{{menu.id}}" ng-style="{width:navWidth}" ng-mouseover="submenuOver()" ng-mouseleave="submenuLeave()" ng-class="{\'item-with-ul\':menu.children && menu.children.length>0, \'flexnav-show\':menu.show}">'+
                    '<a href="{{options.basePath+\'/\'+menu.id}}" title="{{ menu.title }}" ng-style="getStyle()" ng-click="options.onItemClick($event, menu)">{{ menu.title }}</a>'+
                    '<ul ng-if="menu.children && menu.children.length>0">'+
                    '<flexnav-submenu ng-repeat="item in menu.children" menu="item" nav-style="navStyle" level="childrenLevel" siblings="menu.children" lg-screen="lgScreen"></flexnav-submenu>'+
                    '</ul>'+
                    '<span ng-if="menu.children && menu.children.length>0" ng-style="{\'color\':(menu.isSection?navStyle.sectionFontColor:navStyle.menuFontColor)}" class="touch-button" ng-click="onSubmenuClicked(menu, $event)">'+
                    '<i ng-if="level==0||!lgScreen" class="fa fa-angle-down"></i>'+
                    '<i ng-if="level>0&&lgScreen" class="fa fa-angle-right"></i>'+
                    '</span>'+
                    '</li>'
            )
        }
    ]);

    /**
     * Directives
     */

        // Main flexnav directive
    module.directive('flexnav', [
        'flexnav.options', 'flexnav.utils', '$window', '$document', function(flexnavOptions, flexnavUtils, $window, $document) {
            return {
                scope: {
                    menu: '=',
                    options: '=',
                    breakpoint: '=',
                    navStyle: '='
                },
                controller: function($scope, $element, $attrs) {
                    var breakpoint = parseInt($scope.breakpoint);
                    $scope.breakpoint = breakpoint;
                    var w = angular.element($window);
                    $scope.getWindowWidth = function () {
                        return w.width();
                    };
                    $scope.lgScreen = w.width()>=breakpoint;
                    $scope.navWidth = ($scope.lgScreen==true)?(100/$scope.menu.children.length+'%'):'100%';
                    $scope.$watch($scope.getWindowWidth, function (newValue, oldValue) {
                        $scope.lgScreen = w.width()>=breakpoint;
                        $scope.navWidth = ($scope.lgScreen==true)?(100/$scope.menu.children.length+'%'):'100%';
                    });
                    w.bind('resize', function () {
                        $scope.$apply();
                    });
                    $document.on('click', function(event){
                        var isDocumentClicked = $element.find(event.target).length<=0;
                        if (isDocumentClicked) {
                            for(var i=0; i<$scope.menu.children.length; i++) {
                                $scope.menu.children[i].show = false;
                            }
                            if (!$scope.lgScreen) {
                                $scope.mobileOpen = false;
                            }
                            $scope.$apply();
                        }
                    });

                    $scope.mobileOpen = false;
                    $scope.toggleMobileMenu = function() {
                        $scope.mobileOpen = !$scope.mobileOpen;
                    };

                    var options;
                    $scope.options = options = angular.extend(flexnavOptions, $scope.options);
                    $scope.level = 0;
                    this.GetOptions = function() {
                        return options;
                    };
                    $scope.hovered = false;
                    $scope.getStyle = function () {
                        return flexnavUtils.getStyle($scope.navStyle, $scope.menu, $scope.hovered);
                    };
                    $scope.menuOver = function() {
                        $scope.hovered = true;
                    };
                    $scope.menuLeave = function() {
                        $scope.hovered = false;
                    };
                },
                templateUrl: 'flexnav/main_menu.tpl',
                restrict: 'E',
                replace: true
            };
        }
    ]);

    //Submenu directive
    module.directive('flexnavSubmenu', [
        '$compile', 'flexnav.utils', function($compile, $flexnavUtils) {
            return {
                scope: {
                    menu: '=',
                    level: '=',
                    lgScreen: '=',
                    siblings: '=',
                    navWidth:'=',
                    navStyle:'='
                },
                compile: function compile(tElement, tAttr, transclude) {
                    var compiledContents, contents, options;

                    contents = tElement.contents().remove();
                    compiledContents = null;
                    return {
                        pre: function preLink(scope, iElement, iAttr, ctrl) {
                            if (!compiledContents) {
                                compiledContents = $compile(contents);
                            }
                            compiledContents(scope, function(clone, scope) {
                                return iElement.append(clone);
                            });
                        },
                        post: function postLink(scope, element, attr, ctrl) {
                            var options,
                                _this = this;
                            scope.menu.show = false;
                            scope.options = options = ctrl.GetOptions();
                            scope.correctionWidth = 0;
                            scope.childrenLevel = scope.level + 1;
                            scope.onSubmenuClicked = function(item, $event) {
                                item.show = !item.show;
                                if(scope.lgScreen) {
                                    for (var i=0; i<scope.siblings.length; i++) {
                                        if(scope.siblings[i] != item) {
                                            scope.siblings[i].show = false;
                                        }
                                    }
                                }
                            };
                            var watchDisabled = true;
                            scope.$watch('menu.show', function(newValue){
                                if(!watchDisabled) {
                                    if (newValue == true) {
                                        scope.$emit('submenuShown');
                                    } else {
                                        scope.$broadcast('submenuHidden');
                                    }
                                }
                                watchDisabled = false;
                            });
                            scope.$on('submenuShown', function() {
                                scope.menu.show = true;
                            });
                            scope.$on('submenuHidden', function() {
                                scope.menu.show = false;
                            });
                            scope.hovered = false;
                            scope.getStyle = function () {
                                return $flexnavUtils.getStyle(scope.navStyle, scope.menu, scope.hovered);
                            };
                            scope.submenuOver = function() {
                                scope.hovered = true;
                            };
                            scope.submenuLeave = function() {
                                scope.hovered = false;
                            };
                        }
                    }
                },
                templateUrl: 'flexnav/sub_menu.tpl',
                require: '^flexnav',
                restrict: 'EA',
                replace: true
            };
        }
    ]);

    //Utilities
    module.factory('flexnav.utils', function() {
       var getStyle;
       getStyle = function (navStyle, menu, hovered){
           var style = {
               color:(menu.isSection?navStyle.sectionFontColor:navStyle.menuFontColor),
               'border-color':navStyle.menuBorderColor,
               'background-color':hovered?navStyle.menuHoverColor:(menu.isSection?navStyle.sectionBgColor:navStyle.menuBgColor),
               'font-weight': navStyle.menuFontWeight,
               'font-family': navStyle.menuFontFamily,
               'font-size': navStyle.menuFontSize,
               'font-style': navStyle.menuFontStyle
           };
           return style;
       };

       return {getStyle: getStyle};
    });

    // Options value object
    module.value('flexnav.options', {
        'menuButtonName': 'Menu',
        'buttonClass': 'menu-button',
        'calcItemWidths': false,
        'fullScreen': true,
        'onItemClick': function() {}
    });

}).call(this);