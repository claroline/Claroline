(function(){
    'use strict';

    angular
        .module('ui.flexnav')
        .factory('flexnav.utils', function() {
            var service = {
                getStyle: getStyle
            }

            return service;
            ///////////////////

            function getStyle(navStyle, menu, hovered){
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
        });
})();