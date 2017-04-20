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

export default class UsersRegistrationModalCtrl {
  constructor($http, $uibModalInstance, NgTableParams, sessionId, userType, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.sessionId = sessionId
    this.userType = userType
    this.callback = callback
    this.users = []
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.users}
    )
    this.errorMessages = []
    this.loadUsers()
  }

  loadUsers() {
    const route = Routing.generate('api_get_session_unregistered_users', {session: this.sessionId, userType: this.userType})
    this.$http.get(route).then(d => {
      if (d['status'] === 200) {
        this.users.splice(0, this.users.length)
        const users = JSON.parse(d['data'])
        users.forEach(u => {
          this.users.push(u)
        })
      }
    })
  }

  registerUser(userId) {
    this.errorMessages = []
    const route = Routing.generate('api_post_session_user_registration', {session: this.sessionId, user: userId, userType: this.userType})
    this.$http.post(route).then(d => {
      if (d['status'] === 200) {
        const datas = d['data']

        if (datas['status'] === 'success') {
          this.callback(datas['sessionUsers'])
          const index = this.users.findIndex(u => u['id'] === userId)

          if (index > -1) {
            this.users.splice(index, 1)
            this.tableParams.reload()
          }
        } else if (datas['status'] === 'failed') {
          const msg = Translator.trans(
            'required_places_msg',
            {remainingPlaces: datas['datas']['remainingPlaces'], requiredPlaces: datas['datas']['requiredPlaces']},
            'cursus'
          )
          this.errorMessages.push(msg)
        }
      }
    })
  }
}
