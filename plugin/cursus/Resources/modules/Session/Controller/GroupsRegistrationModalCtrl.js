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

export default class GroupsRegistrationModalCtrl {
  constructor($http, $uibModalInstance, NgTableParams, sessionId, groupType, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.sessionId = sessionId
    this.groupType = groupType
    this.callback = callback
    this.groups = []
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.groups}
    )
    this.errorMessages = []
    this.loadGroups()
  }

  loadGroups() {
    const route = Routing.generate('api_get_session_unregistered_groups', {session: this.sessionId, groupType: this.groupType})
    this.$http.get(route).then(d => {
      if (d['status'] === 200) {
        this.groups.splice(0, this.groups.length)
        const groups = JSON.parse(d['data'])
        groups.forEach(g => {
          this.groups.push(g)
        })
      }
    })
  }

  registerGroup(groupId) {
    this.errorMessages = []
    const route = Routing.generate('api_post_session_group_registration', {session: this.sessionId, group: groupId, groupType: this.groupType})
    this.$http.post(route).then(d => {
      if (d['status'] === 200) {
        const datas = d['data']

        if (datas['status'] === 'success') {
          this.callback(datas['sessionGroup'], datas['sessionUsers'])
          const index = this.groups.findIndex(g => g['id'] === groupId)

          if (index > -1) {
            this.groups.splice(index, 1)
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
