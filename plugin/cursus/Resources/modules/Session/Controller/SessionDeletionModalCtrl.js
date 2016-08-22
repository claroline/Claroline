/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class SessionDeletionModalCtrl {
  constructor($http, $uibModalInstance, sessionId, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.sessionId = sessionId
    this.withWorkspaceDeletion = false
    this.callback = callback
  }

  submit() {
    const mode = this.withWorkspaceDeletion ? 1 : 0
    const route = Routing.generate('api_delete_session', {session: this.sessionId, mode: mode})
    this.$http.delete(route).then(d => {
      if (d['status'] === 200) {
        this.callback(d['data'])
        this.$uibModalInstance.close(d['data'])
      }
    })
  }
}
