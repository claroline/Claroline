/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
import angular from 'angular/index'
import $ from 'jquery'

export default class AdminHomeTabsConfigCtrl {

  constructor($http, $stateParams, $state, HomeTabService, WidgetService) {
    this.$http = $http
    this.$state = $state
    this.HomeTabService = HomeTabService
    this.WidgetService = WidgetService
    this.tabId = $stateParams.tabId
    this.adminHomeTabs = HomeTabService.getAdminHomeTabs()
    this.homeTabsOptions = HomeTabService.getOptions()
    this.widgets = WidgetService.getWidgets()
    this.widgetsDisplayOptions = WidgetService.getWidgetsDisplayOptions()
    this.gridsterOptions = WidgetService.getGridsterOptions()
    this.initialize()
    this.initializeDragAndDrop()
  }

  initialize() {
    this.WidgetService.setType('admin')
    this.HomeTabService.loadAdminHomeTabs(this.tabId)
  }

  initializeDragAndDrop () {
    angular.element('#admin-home-tabs-list').sortable({
      items: '.home-tab',
      cursor: 'move'
    })

    angular.element('#admin-home-tabs-list').on('sortupdate', (event, ui) => {
      const hcId = $(ui.item).data('hometab-config-id')
      let nextHcId = -1
      const nextElement = $(ui.item).next()

      if (nextElement !== undefined && nextElement.hasClass('home-tab')) {
        nextHcId = nextElement.data('hometab-config-id')
      }
      const route = Routing.generate(
        'api_post_admin_home_tab_config_reorder',
        {homeTabConfig: hcId, nextHomeTabConfigId: nextHcId, homeTabType: 'desktop'}
      )
      this.$http.post(route)
    })
  }

  showTab(tabId, tabConfigId) {
    this.$state.go('tab', {tabId: parseInt(tabId)}, {location: 'replace', inherit: false, reload: false, notify: false})
    this.homeTabsOptions['selectedTabId'] = tabId
    this.homeTabsOptions['selectedTabConfigId'] = tabConfigId
    this.WidgetService.loadAdminWidgets(tabId)
  }

  createAdminHomeTab() {
    this.HomeTabService.createAdminHomeTab()
  }

  editAdminHomeTab($event, tabConfigId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.editAdminHomeTab(tabConfigId)
  }

  deleteAdminHomeTab($event, tabConfigId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.deleteAdminHomeTab(tabConfigId)
  }

  createAdminWidget(tabId) {
    this.WidgetService.createAdminWidget(tabId)
  }

  editAdminWidget($event, widgetInstanceId, widgetHomeTabConfigId, widgetDisplayId, configurable) {
    $event.preventDefault()
    $event.stopPropagation()
    this.WidgetService.editAdminWidget(widgetInstanceId, widgetHomeTabConfigId, widgetDisplayId, configurable)
  }

  deleteAdminWidget($event, widgetHTCId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.WidgetService.deleteAdminWidget(widgetHTCId)
  }
}