/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/
import angular from 'angular/index'

export default class WidgetService {
  constructor($http, $sce, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$sce = $sce
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.widgets = []
    this.options = {
      canEdit: false
    }
    this.type = 'desktop'
    this.widgetsDisplayOptions = {}
    this.widgetHasChanged = false
    this.gridsterOptions = {
      columns: 12,
      floating: true,
      margins: [15, 15], // same as bootstrap grid. this should not be hardcoded
      outerMargin: false,
      resizable: {
        enabled: false,
        handles: ['ne', 'se', 'sw', 'nw'],
        start: () => {},
        resize: () => {
          this.widgetHasChanged = true
        },
        stop: () => {
          if (this.widgetHasChanged) {
            this.widgetHasChanged = false
            this._updateWidgetsDisplay()
          }
        }
      },
      draggable: {
        enabled: false,
        handle: '.widget-header',
        start: () => {},
        drag: () => {
          this.widgetHasChanged = true
        },
        stop: () => {
          if (this.widgetHasChanged) {
            this.widgetHasChanged = false
            this._updateWidgetsDisplay()
          }
        }
      }
    }
    this._addWidgetCallback = this._addWidgetCallback.bind(this)
    this._updateWidgetsDisplay = this._updateWidgetsDisplay.bind(this)
    this._removeWidgetCallback = this._removeWidgetCallback.bind(this)
  }

  _addWidgetCallback(d) {
    const data = this.parseWidgetDatas(d)
    this.widgetsDisplayOptions[data['displayId']] = {
      id: data['displayId'],
      row: data['row'],
      col: data['col'],
      sizeX: data['sizeX'],
      sizeY: data['sizeY']
    }
    this.widgets.push(data)
    this.loadWidgetContent(data['instanceId'])
    this._updateWidgetsDisplay()
  }

  _updateWidgetsDisplay() {
    if (this.type === 'desktop') {
      this.checkDesktopWidgetsDisplayOptions()
    } else if (this.type === 'admin') {
      this.checkAdminWidgetsDisplayOptions()
    } else if (this.type === 'workspace') {
      this.checkWorkspaceWidgetsDisplayOptions()
    }
  }

  _removeWidgetCallback(d) {
    const data = JSON.parse(d)
    if (data['id']) {
      const index = this.widgets.findIndex(w => data['id'] === w['instanceId'])

      if (index > -1) {
        this.widgets.splice(index, 1)
      }
      this._updateWidgetsDisplay()
    }
  }

  getWidgets() {
    return this.widgets
  }

  getWidgetsDisplayOptions() {
    return this.widgetsDisplayOptions
  }

  getOptions() {
    return this.options
  }

  getGridsterOptions() {
    return this.gridsterOptions
  }

  setType(type) {
    this.type = type
  }

  parseWidgetDatas(datas) {
    let widgetDatas = {}
    const config = datas['config'] ? JSON.parse(datas['config']) : null
    const display = datas['display'] ? JSON.parse(datas['display']) : null
    widgetDatas['configurable'] = datas['configurable'] ? datas['configurable'] : null

    if (config !== null) {
      widgetDatas['configId'] = config['id']
      widgetDatas['locked'] = config['locked']
      widgetDatas['visible'] = config['visible']
      widgetDatas['type'] = config['type']
    }

    if (display !== null) {
      widgetDatas['displayId'] = display['id']
      widgetDatas['row'] = display['row'] >= 0 ? display['row'] : null
      widgetDatas['col'] = display['column'] >= 0 ? display['column'] : null
      widgetDatas['sizeX'] = display['width']
      widgetDatas['sizeY'] = display['height']
      widgetDatas['color'] = display['color']
      widgetDatas['textTitleColor'] = display['details']['textTitleColor'] ? display['details']['textTitleColor'] : null

      widgetDatas['instanceId'] = display['widgetInstance']['id']
      widgetDatas['instanceName'] = this.$sce.trustAsHtml(display['widgetInstance']['name'])
      widgetDatas['instanceIcon'] = display['widgetInstance']['icon']

      widgetDatas['widgetId'] = display['widgetInstance']['widget']['id']
      widgetDatas['widgetName'] = display['widgetInstance']['widget']['name']
    }

    return widgetDatas
  }


  generateWidgetsDatas(datas) {
    let widgetsDatas = []
    datas.forEach(d => {
      let widgetDatas = {}
      const config = JSON.parse(d['config'])
      const display = JSON.parse(d['display'])
      widgetDatas['content'] = d['content']
      widgetDatas['configurable'] = d['configurable']

      widgetDatas['configId'] = config['id']
      widgetDatas['locked'] = config['locked']
      widgetDatas['visible'] = config['visible']
      widgetDatas['type'] = config['type']

      widgetDatas['instanceId'] = config['widgetInstance']['id']
      widgetDatas['instanceName'] = config['widgetInstance']['name']
      widgetDatas['instanceIcon'] = config['widgetInstance']['icon']

      widgetDatas['widgetId'] = config['widgetInstance']['widget']['id']
      widgetDatas['widgetName'] = config['widgetInstance']['widget']['name']

      widgetDatas['displayId'] = display['id']
      widgetDatas['row'] = display['row'] >= 0 ? display['row'] : null
      widgetDatas['col'] = display['column'] >= 0 ? display['column'] : null
      widgetDatas['sizeX'] = display['width']
      widgetDatas['sizeY'] = display['height']
      widgetDatas['color'] = display['color']
      widgetDatas['textTitleColor'] = display['details']['textTitleColor'] ? display['details']['textTitleColor'] : null

      widgetsDatas.push(widgetDatas)
    })

    return widgetsDatas
  }

  loadDesktopWidgets(tabId, isEditionEnabled) {
    this.options['canEdit'] = false

    if (tabId === 0) {
      this.widgets.splice(0, this.widgets.length)
    } else {
      const route = Routing.generate('api_get_desktop_widgets_display', {homeTab: tabId})
      this.$http.get(route).then(datas => {
        if (datas['status'] === 200) {
          this.options['canEdit'] = isEditionEnabled && !datas['data']['isLockedHomeTab']
          this.widgets.splice(0, this.widgets.length)
          angular.merge(this.widgets, this.generateWidgetsDatas(datas['data']['widgets']))
          this.generateWidgetsDisplayOptions()
          this.checkDesktopWidgetsDisplayOptions()
          this.updateGristerEdition()
          this.secureWidgetsContents()
        }
      })
    }
  }

  loadAdminWidgets(tabId) {
    if (tabId === 0) {
      this.widgets.splice(0, this.widgets.length)
    } else {
      const route = Routing.generate('api_get_admin_widgets_display', {homeTab: tabId})
      this.$http.get(route).then(datas => {
        if (datas['status'] === 200) {
          this.widgets.splice(0, this.widgets.length)
          angular.merge(this.widgets, this.generateWidgetsDatas(datas['data']))
          this.generateWidgetsDisplayOptions()
          this.checkAdminWidgetsDisplayOptions()
          this.switchGridsterEdition(true)
          this.secureWidgetsContents()
        }
      })
    }
  }

  loadWorkspaceWidgets(tabId) {
    if (tabId === 0) {
      this.widgets.splice(0, this.widgets.length)
    } else {
      const route = Routing.generate('api_get_workspace_widgets_display', {homeTab: tabId})
      this.$http.get(route).then(datas => {
        if (datas['status'] === 200) {
          this.widgets.splice(0, this.widgets.length)
          angular.merge(this.widgets, this.generateWidgetsDatas(datas['data']))
          this.generateWidgetsDisplayOptions()
          this.checkWorkspaceWidgetsDisplayOptions()
          this.updateGristerEdition()
          this.secureWidgetsContents()
        }
      })
    }
  }

  updateWidget(d) {
    const data = this.parseWidgetDatas(d)
    const index = this.widgets.findIndex(w => w['instanceId'] === data['instanceId'])

    if (index > -1) {
      this.widgets[index]['instanceName'] = data['instanceName']
      this.widgets[index]['color'] = data['color']
      this.widgets[index]['textTitleColor'] = data['textTitleColor']

      if (data['visible'] !== undefined) {
        this.widgets[index]['visible'] = data['visible']
      }

      if (data['locked'] !== undefined) {
        this.widgets[index]['locked'] = data['locked']
      }
    }
  }

  secureWidgetsContents() {
    this.widgets.forEach(w => {
      w['instanceName'] = this.$sce.trustAsHtml(w['instanceName'])
      w['content'] = this.$sce.trustAsHtml(w['content'])
    })
  }

  secureDatas(datas) {
    return this.$sce.trustAsHtml(datas)
  }

  loadWidgetContent(widgetInstanceId) {
    const index = this.widgets.findIndex(w => w['instanceId'] === widgetInstanceId)

    if (index > -1) {
      const route = Routing.generate('claro_widget_instance_content', {widgetInstance: widgetInstanceId})
      this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          this.widgets[index]['content'] = this.secureDatas(d['data'])
        }
      })
    }
  }

  generateWidgetsDisplayOptions() {
    this.widgets.forEach(w => {
      const displayId = w['displayId']
      this.widgetsDisplayOptions[displayId] = {
        id: w['displayId'],
        row: w['row'],
        col: w['col'],
        sizeX: w['sizeX'],
        sizeY: w['sizeY']
      }
    })
  }

  checkDesktopWidgetsDisplayOptions() {
    let modifiedWidgets = []

    this.widgets.forEach(w => {
      const displayId = w['displayId']

      if (w['row'] !== this.widgetsDisplayOptions[displayId]['row'] ||
        w['col'] !== this.widgetsDisplayOptions[displayId]['col'] ||
        w['sizeX'] !== this.widgetsDisplayOptions[displayId]['sizeX'] ||
        w['sizeY'] !== this.widgetsDisplayOptions[displayId]['sizeY']) {

        const widgetDatas = {
          id: displayId,
          row: w['row'],
          col: w['col'],
          sizeX: w['sizeX'],
          sizeY: w['sizeY']
        }
        modifiedWidgets.push(widgetDatas)
      }
    })

    if (modifiedWidgets.length > 0) {
      const json = JSON.stringify(modifiedWidgets)
      const route = Routing.generate('api_put_desktop_widget_display_update', {datas: json})
      this.$http.put(route).then(
        (datas) => {
          if (datas['status'] === 200) {
            const displayDatas = datas['data']

            displayDatas.forEach(d => {
              const id = d['id']
              this.widgetsDisplayOptions[id]['row'] = d['row']
              this.widgetsDisplayOptions[id]['col'] = d['col']
              this.widgetsDisplayOptions[id]['sizeX'] = d['sizeX']
              this.widgetsDisplayOptions[id]['sizeY'] = d['sizeY']
            })
          }
        }
      )
    }
  }

  checkAdminWidgetsDisplayOptions() {
    let modifiedWidgets = []

    this.widgets.forEach(w => {
      const displayId = w['displayId']

      if (w['row'] !== this.widgetsDisplayOptions[displayId]['row'] ||
        w['col'] !== this.widgetsDisplayOptions[displayId]['col'] ||
        w['sizeX'] !== this.widgetsDisplayOptions[displayId]['sizeX'] ||
        w['sizeY'] !== this.widgetsDisplayOptions[displayId]['sizeY']) {

        const widgetDatas = {
          id: displayId,
          row: w['row'],
          col: w['col'],
          sizeX: w['sizeX'],
          sizeY: w['sizeY']
        }
        modifiedWidgets.push(widgetDatas)
      }
    })

    if (modifiedWidgets.length > 0) {
      const json = JSON.stringify(modifiedWidgets)
      const route = Routing.generate('api_put_admin_widget_display_update', {datas: json})
      this.$http.put(route).then(
        (datas) => {
          if (datas['status'] === 200) {
            const displayDatas = datas['data']

            displayDatas.forEach(d => {
              const id = d['id']
              this.widgetsDisplayOptions[id]['row'] = d['row']
              this.widgetsDisplayOptions[id]['col'] = d['col']
              this.widgetsDisplayOptions[id]['sizeX'] = d['sizeX']
              this.widgetsDisplayOptions[id]['sizeY'] = d['sizeY']
            })
          }
        }
      )
    }
  }

  checkWorkspaceWidgetsDisplayOptions() {
    let modifiedWidgets = []

    this.widgets.forEach(w => {
      const displayId = w['displayId']

      if (w['row'] !== this.widgetsDisplayOptions[displayId]['row'] ||
        w['col'] !== this.widgetsDisplayOptions[displayId]['col'] ||
        w['sizeX'] !== this.widgetsDisplayOptions[displayId]['sizeX'] ||
        w['sizeY'] !== this.widgetsDisplayOptions[displayId]['sizeY']) {

        const widgetDatas = {
          id: displayId,
          row: w['row'],
          col: w['col'],
          sizeX: w['sizeX'],
          sizeY: w['sizeY']
        }
        modifiedWidgets.push(widgetDatas)
      }
    })

    if (modifiedWidgets.length > 0) {
      const json = JSON.stringify(modifiedWidgets)
      const route = Routing.generate('api_put_workspace_widget_display_update', {datas: json})
      this.$http.put(route).then(
        (datas) => {
          if (datas['status'] === 200) {
            const displayDatas = datas['data']

            displayDatas.forEach(d => {
              const id = d['id']
              this.widgetsDisplayOptions[id]['row'] = d['row']
              this.widgetsDisplayOptions[id]['col'] = d['col']
              this.widgetsDisplayOptions[id]['sizeX'] = d['sizeX']
              this.widgetsDisplayOptions[id]['sizeY'] = d['sizeY']
            })
          }
        }
      )
    }
  }

  updateGristerEdition() {
    const editable = this.options['canEdit']
    this.gridsterOptions['resizable']['enabled'] = editable
    this.gridsterOptions['draggable']['enabled'] = editable
  }

  switchGridsterEdition(editable) {
    this.gridsterOptions['resizable']['enabled'] = editable
    this.gridsterOptions['draggable']['enabled'] = editable
  }

  createUserWidget(tabConfigId) {
    if (!this.isHomeTabLocked) {
      const modal = this.$uibModal.open({
        templateUrl: Routing.generate(
          'api_get_widget_instance_creation_form',
          {htc: tabConfigId}
        ),
        controller: 'DesktopWidgetInstanceCreationModalCtrl',
        controllerAs: 'wfmc',
        resolve: {
          homeTabConfigId: () => { return tabConfigId },
          callback: () => { return this._addWidgetCallback }
        }
      })

      modal.result.then(result => {
        if (!result) {
          return
        } else {
          this._addWidgetCallback(result)
        }
      })
    }
  }

  editUserWidget(widgetInstanceId, widgetDisplayId, configurable) {
    if (!this.isHomeTabLocked) {
      this.$uibModal.open({
        templateUrl: Routing.generate(
          'api_get_widget_instance_edition_form',
          {wdc: widgetDisplayId}
        ) + '?bust=' + Math.random().toString(36).slice(2),
        controller: 'DesktopWidgetInstanceEditionModalCtrl',
        controllerAs: 'wfmc',
        resolve: {
          widgetInstanceId: () => { return widgetInstanceId },
          widgetDisplayId: () => { return widgetDisplayId },
          configurable: () => { return configurable },
          contentConfig: () => { return null }
        }
      })
    }
  }

  deleteUserWidget(widgetHTCId) {
    if (!this.isHomeTabLocked) {
      const url = Routing.generate(
        'api_delete_desktop_widget_home_tab_config',
        {widgetHomeTabConfig: widgetHTCId}
      )

      this.ClarolineAPIService.confirm(
        {url, method: 'DELETE'},
        this._removeWidgetCallback,
        Translator.trans('widget_home_tab_delete_confirm_title', {}, 'platform'),
        Translator.trans('widget_home_tab_delete_confirm_message', {}, 'platform')
      )
    }
  }

  hideAdminWidget(widgetHTCId) {
    if (!this.isHomeTabLocked) {
      const url = Routing.generate(
        'api_put_desktop_widget_home_tab_config_visibility_change',
        {widgetHomeTabConfig: widgetHTCId}
      )

      this.ClarolineAPIService.confirm(
        {url, method: 'PUT'},
        this._removeWidgetCallback,
        Translator.trans('widget_home_tab_delete_confirm_title', {}, 'platform'),
        Translator.trans('widget_home_tab_delete_confirm_message', {}, 'platform')
      )
    }
  }

  createAdminWidget(tabId) {
    const modal = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_admin_widget_instance_creation_form'),
      controller: 'AdminWidgetInstanceCreationModalCtrl',
      controllerAs: 'wfmc',
      resolve: {
        homeTabId: () => { return tabId },
        callback: () => { return this._addWidgetCallback }
      }
    })

    modal.result.then(result => {
      if (!result) {
        return
      } else {
        this._addWidgetCallback(result)
      }
    })
  }

  editAdminWidget(widgetInstanceId, widgetHomeTabConfigId, widgetDisplayId, configurable) {
    this.$uibModal.open({
      templateUrl: Routing.generate(
        'api_get_admin_widget_instance_edition_form',
        {whtc: widgetHomeTabConfigId, wdc: widgetDisplayId}
      ) + '?bust=' + Math.random().toString(36).slice(2),
      controller: 'AdminWidgetInstanceEditionModalCtrl',
      controllerAs: 'wfmc',
      resolve: {
        widgetInstanceId: () => { return widgetInstanceId },
        widgetHomeTabConfigId: () => { return widgetHomeTabConfigId },
        widgetDisplayId: () => { return widgetDisplayId },
        configurable: () => { return configurable },
        contentConfig: () => { return null }
      }
    })
  }

  deleteAdminWidget(widgetHTCId) {
    const url = Routing.generate(
      'api_delete_admin_widget_home_tab_config',
      {widgetHomeTabConfig: widgetHTCId}
    )

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      this._removeWidgetCallback,
      Translator.trans('widget_home_tab_delete_confirm_title', {}, 'platform'),
      Translator.trans('widget_home_tab_delete_confirm_message', {}, 'platform')
    )
  }

  createWorkspaceWidget(tabId) {
    const modal = this.$uibModal.open({
      templateUrl: Routing.generate('api_get_workspace_widget_instance_creation_form', {homeTab: tabId}),
      controller: 'WorkspaceWidgetInstanceCreationModalCtrl',
      controllerAs: 'wfmc',
      resolve: {
        homeTabId: () => { return tabId },
        callback: () => { return this._addWidgetCallback }
      }
    })

    modal.result.then(result => {
      if (!result) {
        return
      } else {
        this._addWidgetCallback(result)
      }
    })
  }

  editWorkspaceWidget(widgetInstanceId, widgetHomeTabConfigId, widgetDisplayId, configurable) {
    this.$uibModal.open({
      templateUrl: Routing.generate(
        'api_get_workspace_widget_instance_edition_form',
        {whtc: widgetHomeTabConfigId, wdc: widgetDisplayId}
      ) + '?bust=' + Math.random().toString(36).slice(2),
      controller: 'WorkspaceWidgetInstanceEditionModalCtrl',
      controllerAs: 'wfmc',
      resolve: {
        widgetInstanceId: () => { return widgetInstanceId },
        widgetHomeTabConfigId: () => { return widgetHomeTabConfigId },
        widgetDisplayId: () => { return widgetDisplayId },
        configurable: () => { return configurable },
        contentConfig: () => { return null }
      }
    })
  }

  deleteWorkspaceWidget(widgetHTCId) {
    const url = Routing.generate(
      'api_delete_workspace_widget_home_tab_config',
      {widgetHomeTabConfig: widgetHTCId}
    )

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      this._removeWidgetCallback,
      Translator.trans('widget_home_tab_delete_confirm_title', {}, 'platform'),
      Translator.trans('widget_home_tab_delete_confirm_message', {}, 'platform')
    )
  }
}