/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class AdminHomeTabCreationModalCtrl {
  constructor($http, $uibModal, $uibModalInstance, ClarolineAPIService, callback) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.ClarolineAPIService = ClarolineAPIService
    this.callback = callback
    this.homeTab = {}
  }

  submit() {
    let data = this.ClarolineAPIService.formSerialize('home_tab_form', this.homeTab)
    const route = Routing.generate('api_post_admin_home_tab_creation', {'_format': 'html'})
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
            controller: 'AdminHomeTabCreationModalCtrl',
            controllerAs: 'htfmc',
            bindToController: true,
            resolve: {
              callback: () => { return this.callback },
              homeTab: () => { return this.homeTab }
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