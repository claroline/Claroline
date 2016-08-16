/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class CursusUsersUnregistrationModalCtrl {
        
  constructor($http, $uibModalInstance, cursusId, usersIdsTxt, callBack) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.cursusId = cursusId
    this.usersIdsTxt = usersIdsTxt
    this.callBack = callBack
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  confirmModal() {
    const route = Routing.generate('api_delete_cursus_users', {cursus: this.cursusId, usersIdsTxt: this.usersIdsTxt})
    this.$http.delete(route).then(datas => {
      if (datas['status'] === 200) {
        let usersIds = this.usersIdsTxt.split(',')

        for (let i = 0; i < usersIds.length; i++) {
          usersIds[i] = parseInt(usersIds[i])
        }
        this.callBack(usersIds)
        this.closeModal()
      }
    })
  }
}