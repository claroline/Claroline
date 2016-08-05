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

export default class DesktopHomeMainCtrl {
  constructor($http, $stateParams, $state, HomeTabService, WidgetService) {
    this.$http = $http
    this.$state = $state
    this.HomeTabService = HomeTabService
    this.WidgetService = WidgetService
    this.tabId = parseInt($stateParams.tabId)
    this.adminHomeTabs = HomeTabService.getAdminHomeTabs()
    this.userHomeTabs = HomeTabService.getUserHomeTabs()
    this.workspaceHomeTabs = HomeTabService.getWorkspaceHomeTabs()
    this.homeTabsOptions = HomeTabService.getOptions()
    this.widgets = WidgetService.getWidgets()
    this.widgetsOptions = WidgetService.getOptions()
    this.widgetsDisplayOptions = WidgetService.getWidgetsDisplayOptions()
    this.editionMode = false
    this.isHomeLocked = true
    this.gridsterOptions = WidgetService.getGridsterOptions()
    this.initialize()
    this.initializeDragAndDrop()
  }

  initialize() {
    const route = Routing.generate('api_get_desktop_options')
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.isHomeLocked = datas['data']['isHomeLocked']
        this.editionMode = datas['data']['editionMode']
        this.homeTabsOptions['canEdit'] = !this.isHomeLocked && this.editionMode

        if (this.tabId === -1) {
          this.tabId = parseInt(DesktopHomeMainCtrl._getGlobal('tabId'))
          this.$state.go('tab', {tabId: this.tabId}, {location: 'replace', inherit: false, reload: false, notify: false})
        }
        this.HomeTabService.loadDesktopHomeTabs(this.tabId)
      }
    })
  }

  initializeDragAndDrop () {
    angular.element('#desktop-home-tabs-list').sortable({
      items: '.movable-home-tab',
      cursor: 'move'
    })

    angular.element('#desktop-home-tabs-list').on('sortupdate', (event, ui) => {
      const hcId = $(ui.item).data('hometab-config-id')
      let nextHcId = -1
      const nextElement = $(ui.item).next()

      if (nextElement !== undefined && nextElement.hasClass('movable-home-tab')) {
        nextHcId = nextElement.data('hometab-config-id')
      }
      const route = Routing.generate(
        'api_post_desktop_home_tab_config_reorder',
        {homeTabConfig: hcId, nextHomeTabConfigId: nextHcId}
      )
      this.$http.post(route)
    })
  }

  toggleEditionMode() {
    const route = Routing.generate('api_put_desktop_home_edition_mode_toggle')
    this.$http.put(route).then(datas => {
      if (datas['status'] === 200) {
        this.editionMode = datas['data']
        this.homeTabsOptions['canEdit'] = !this.isHomeLocked && this.editionMode
        this.widgetsOptions['canEdit'] = !this.isHomeLocked && this.editionMode && !this.homeTabsOptions['selectedTabIsLocked']
        this.WidgetService.updateGristerEdition()
      }
    })
  }

  showTab(tabId, tabConfigId, tabIsLocked) {
    this.$state.go('tab', {tabId: parseInt(tabId)}, {location: 'replace', inherit: false, reload: false, notify: false})
    this.homeTabsOptions['selectedTabId'] = tabId
    this.homeTabsOptions['selectedTabConfigId'] = tabConfigId
    this.homeTabsOptions['selectedTabIsLocked'] = tabIsLocked
    this.WidgetService.loadDesktopWidgets(tabId, !this.isHomeLocked && this.editionMode)
  }

  createUserHomeTab() {
    this.HomeTabService.createUserHomeTab()
  }

  editUserHomeTab($event, tabId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.editUserHomeTab(tabId)
  }

  hideAmdinHomeTab($event, tabConfigId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.hideAmdinHomeTab(tabConfigId)
  }

  deleteUserHomeTab($event, tabConfigId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.deleteUserHomeTab(tabConfigId)
  }

  deletePinnedWorkspaceHomeTab($event, tabConfigId) {
    $event.preventDefault()
    $event.stopPropagation()
    this.HomeTabService.deletePinnedWorkspaceHomeTab(tabConfigId)
  }

  createUserWidget(tabConfigId) {

    if (!this.isHomeLocked && this.editionMode) {
      this.WidgetService.createUserWidget(tabConfigId)
    }
  }

  editUserWidget($event, widgetInstanceId, widgetDisplayId, configurable) {
    $event.preventDefault()
    $event.stopPropagation()

    if (!this.isHomeLocked && this.editionMode) {
      this.WidgetService.editUserWidget(widgetInstanceId, widgetDisplayId, configurable)
    }
  }

  deleteUserWidget($event, widgetHTCId) {
    $event.preventDefault()
    $event.stopPropagation()

    if (!this.isHomeLocked && this.editionMode) {
      this.WidgetService.deleteUserWidget(widgetHTCId)
    }
  }

  hideAdminWidget($event, widgetHTCId) {
    $event.preventDefault()
    $event.stopPropagation()

    if (!this.isHomeLocked && this.editionMode) {
      this.WidgetService.hideAdminWidget(widgetHTCId)
    }
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}