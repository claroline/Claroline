import editor from '#/main/core/tools/home/walkthroughs/editor'
import widget from '#/main/core/tools/home/walkthroughs/widget'
import widgetList from '#/main/core/tools/home/walkthroughs/widget-list'
import widgetResource from '#/main/core/tools/home/walkthroughs/widget-resource'
import widgetSimple from '#/main/core/tools/home/walkthroughs/widget-simple'

function getWalkthroughs() {
  return [
    editor,
    widget,
    widgetSimple,
    widgetList,
    widgetResource
  ]
}

export {
  getWalkthroughs
}
