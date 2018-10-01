import editor from '#/main/core/tools/home/walkthroughs/editor'
import widgetList from '#/main/core/tools/home/walkthroughs/widget-list'
import widgetResource from '#/main/core/tools/home/walkthroughs/widget-resource'
import widgetSimple from '#/main/core/tools/home/walkthroughs/widget-simple'

function getWalkthroughs(currentTab, update) {
  return [
    editor(currentTab, update),
    widgetSimple(currentTab, update),
    widgetList(currentTab, update),
    widgetResource(currentTab, update)
  ]
}

export {
  getWalkthroughs
}
