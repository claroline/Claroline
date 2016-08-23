/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class CursusGroupUnregistrationModalCtrl {
        
  constructor($http, $uibModalInstance, cursusGroupId, groupName, callBack) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.cursusGroupId = cursusGroupId
    this.groupName = groupName
    this.callBack = callBack
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  confirmModal() {
    const route = Routing.generate('api_delete_cursus_group', {cursusGroup: this.cursusGroupId})
    this.$http.delete(route).then(datas => {
      if (datas['status'] === 200) {
        this.callBack(this.cursusGroupId)
        this.closeModal()
      }
    })
  }
}