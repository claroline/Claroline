export default class FlexnavUtils {
  construct() {
  }

  getStyle(styleOptions, menu, hovered) {
    var style = {
      color: (menu.isSection ? styleOptions.sectionFontColor : styleOptions.menuFontColor),
      'border-color': styleOptions.menuBorderColor,
      'background-color': hovered ? styleOptions.menuHoverColor : (menu.isSection ? styleOptions.sectionBgColor : styleOptions.menuBgColor),
      'font-weight': styleOptions.menuFontWeight,
      'font-family': styleOptions.menuFontFamily,
      'font-size': styleOptions.menuFontSize,
      'font-style': styleOptions.menuFontStyle
    };

    return style;
  }

  getNavStyle (styleOptions, width) {
    if (styleOptions.menuOrientation == 'horizontal') {
      return {
        'width': width,
        'background-color': styleOptions.menuBgColor,
        'color': styleOptions.menuFontColor,
        'font-size': styleOptions.menuFontSize + 'px',
        'font-family': styleOptions.menuFontFamily
      }
    } else {
      return {}
    }
  }

  getCaretStyle (styleOptions, menu) {
    return {
      'color':menu.isSection?styleOptions.sectionFontColor:styleOptions.menuFontColor
    }
  }

  getToggleStyle (styleOptions) {
    return {
      'color': styleOptions.menuFontColor,
      'background-color': styleOptions.menuBgColor,
      'border-color': styleOptions.menuFontColor
    }
  }
}