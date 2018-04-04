/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class DesktopWidgetInstanceCreationModalCtrl {
  constructor($http, $uibModal, $uibModalInstance, ClarolineAPIService, homeTabConfigId, callback) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.ClarolineAPIService = ClarolineAPIService
    this.homeTabConfigId = homeTabConfigId
    this.callback = callback
    this.widgetInstance = {}
  }

  submit() {
    let data = this.ClarolineAPIService.formSerialize(
      'widget_instance_config_form',
      this.widgetInstance
    )
    const route = Routing.generate(
      'api_post_desktop_widget_instance_creation',
      {'_format': 'html', htc: this.homeTabConfigId}
    )
    const headers = {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}

    this.$http.post(route, data, headers).then(
      d => {
        this.$uibModalInstance.close(d.data)
      },
      d => {
        if (d.status === 400) {
          this.$uibModalInstance.close()
          const instance = this.$uibModal.open({
            template: d.data,
            controller: 'DesktopWidgetInstanceCreationModalCtrl',
            controllerAs: 'wfmc',
            bindToController: true,
            resolve: {
              homeTabConfigId: () => { return this.homeTabConfigId },
              callback: () => { return this.callback },
              widgetInstance: () => { return this.widgetInstance }
            }
          })

          instance.result.then(result => {
            if (!result) {
              return
            } else {
              this.callback(result)
            }
          })
        }
      }
    )
  }
}