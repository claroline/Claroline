import mainMenuTemplate from './templates/main_menu.tpl.html'
import subMenuTemplate from './templates/sub_menu.tpl.html'

FlexnavTemplates.append.$inject = ['$templateCache']

export default class FlexnavTemplates {
  static append($templateCache) {
    $templateCache.put('flexnav/main_menu.tpl', mainMenuTemplate)
    $templateCache.put('flexnav/sub_menu.tpl', subMenuTemplate)
  }
}