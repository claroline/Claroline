/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class CursusGroupsUnregistrationModalCtrl {

  constructor($http, $uibModalInstance, cursusGroupsIdsTxt, callBack) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.cursusGroupsIdsTxt = cursusGroupsIdsTxt
    this.callBack = callBack
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  confirmModal() {
    const route = Routing.generate('api_delete_cursus_groups', {cursusGroupsIdsTxt: this.cursusGroupsIdsTxt})
    this.$http.delete(route).then(datas => {
      if (datas['status'] === 200) {
        let cursusGroupsIds = this.cursusGroupsIdsTxt.split(',')

        for (let i = 0; i < cursusGroupsIds.length; i++) {
          cursusGroupsIds[i] = parseInt(cursusGroupsIds[i])
        }
        this.callBack(cursusGroupsIds)
        this.closeModal()
      }
    })
  }
}