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

export default class WorkspaceWidgetInstanceEditionModalCtrl {
  constructor($http, $sce, $uibModal, $uibModalInstance, $httpParamSerializer, ClarolineAPIService, WidgetService, widgetInstanceId, widgetHomeTabConfigId, widgetDisplayId, configurable, contentConfig) {
    this.$http = $http
    this.$sce = $sce
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.$httpParamSerializer = $httpParamSerializer
    this.ClarolineAPIService = ClarolineAPIService
    this.WidgetService = WidgetService
    this.widgetInstanceId = widgetInstanceId
    this.widgetHomeTabConfigId = widgetHomeTabConfigId
    this.widgetDisplayId = widgetDisplayId
    this.configurable = configurable
    this.widgetInstance = {}
    this.contentConfig = this.secureHtml(contentConfig)
    this.initializeContentConfigForm()
  }

  initializeContentConfigForm() {
    if (this.contentConfig === null) {
      const route = Routing.generate('api_get_widget_instance_content_configuration_form', {widgetInstance: this.widgetInstanceId})
      this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          this.contentConfig = this.secureHtml(d['data'])
        }
      })
    }
  }

  secureHtml(html) {
    return typeof html === 'string' ? this.$sce.trustAsHtml(html) : html
  }

  submit() {
    if (this.configurable) {
      this.submitContentConfiguration().then(result => {
        if (result === null) {
          this.submitMainForm()
        } else if (result) {
          this.submitMainForm(result)
        }
      })
    } else {
      this.submitMainForm()
    }
  }

  submitMainForm(widgetContentConfigResult = null) {
    let data = this.ClarolineAPIService.formSerialize(
      'widget_instance_config_form',
      this.widgetInstance
    )
    const route = Routing.generate(
      'api_put_workspace_widget_instance_edition',
      {'_format': 'html', wdc: this.widgetDisplayId, whtc: this.widgetHomeTabConfigId}
    )
    const headers = {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
    this.$http.put(route, data, headers).then(
      d => {
        this.WidgetService.updateWidget(d.data)

        if (widgetContentConfigResult) {
          this.contentConfig = this.secureHtml(widgetContentConfigResult)
        } else {
          this.$uibModalInstance.close()
        }
      },
      d => {
        if (d.status === 400) {
          this.$uibModalInstance.close()
          this.$uibModal.open({
            template: d.data,
            controller: 'WorkspaceWidgetInstanceEditionModalCtrl',
            controllerAs: 'wfmc',
            bindToController: true,
            resolve: {
              widgetInstanceId: () => { return this.widgetInstanceId },
              widgetHomeTabConfigId: () => { return this.widgetHomeTabConfigId },
              widgetDisplayId: () => { return this.widgetDisplayId },
              configurable: () => { return this.configurable },
              contentConfig: () => { return widgetContentConfigResult },
              widgetInstance: () => { return this.widgetInstance }
            }
          })
        }
      }
    )
  }

  submitContentConfiguration() {
    const forms = angular.element('.widget-content-config-form')

    if (forms.length > 0) {
      const action = forms[0].action
      const formData = angular.element(forms[0]).serializeArray()
      let data = {}
      formData.forEach(d => {
        if (d['name'].endsWith('[]')) {
          if (data[d['name']] === undefined) {
            data[d['name']] = []
          }
          data[d['name']].push(d['value'])
        } else {
          data[d['name']] = d['value']
        }
      })

      return this.$http.post(
        action,
        this.$httpParamSerializer(data),
        {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
      ).then(d => {
        if (d['status'] === 204) {
          this.WidgetService.loadWidgetContent(this.widgetInstanceId)

          return null
        } else if (d['status'] === 200) {
          return d['data']
        }
      })
    } else {
      return null
    }
  }
}