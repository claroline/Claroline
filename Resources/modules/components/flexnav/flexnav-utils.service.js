export default class FlexnavUtils {
  construct() {

  }

  getStyle(navStyle, menu, hovered) {
    var style = {
      color: (menu.isSection ? navStyle.sectionFontColor : navStyle.menuFontColor),
      'border-color': navStyle.menuBorderColor,
      'background-color': hovered ? navStyle.menuHoverColor : (menu.isSection ? navStyle.sectionBgColor : navStyle.menuBgColor),
      'font-weight': navStyle.menuFontWeight,
      'font-family': navStyle.menuFontFamily,
      'font-size': navStyle.menuFontSize,
      'font-style': navStyle.menuFontStyle
    };

    return style;
  }
}