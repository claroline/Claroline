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
import simpleModalTemplate from '../Partial/simple_modal.html'

export default class CursusRegistrationSessionsModalCtrl {
        
  constructor($http, $state, $uibModal, $uibModalInstance, cursusId, sourceId, sourceType, cursusIdsTxt) {
    this.$http = $http
    this.$state = $state
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.cursusId = cursusId
    this.sourceId = sourceId
    this.sourceType = sourceType
    this.cursusIdsTxt = cursusIdsTxt
    this.sessionsData = []
    this.selectedSessions = []

    this.initialize()
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  confirmModal() {
    let route
    let sessionsIdsTxt = ''

    for (let courseId in this.selectedSessions) {
      sessionsIdsTxt += this.selectedSessions[courseId] + ','
    }
    const length = sessionsIdsTxt.length
    sessionsIdsTxt = (length > 0) ? sessionsIdsTxt.substring(0, length - 1) : '0'

    if (this.sourceType === 'group') {
      route = Routing.generate(
        'api_post_group_register_to_multiple_cursus',
        {
          group: this.sourceId,
          cursusIdsTxt: this.cursusIdsTxt,
          sessionsIdsTxt: sessionsIdsTxt
        }
      )
      this.$http.post(route).then(data => {
        if (data['status'] === 200) {
          this.closeModal()
          const resultsData = data['data']

          if (resultsData['status'] === 'success') {
            this.$state.transitionTo(
              'registration_cursus_management',
              {cursusId: this.cursusId},
              { reload: true, inherit: true, notify: true }
            )
          } else {
            const title = Translator.trans('registration_failed', {}, 'cursus')
            let content = ''
            const errorData = resultsData['datas']

            for (let i = 0; i < errorData.length; i++) {
              content += '<div class="alert alert-danger">' +
                Translator.trans(
                  'session_not_enough_place_msg',
                  {
                    sessionName: errorData[i]['sessionName'],
                    courseTitle: errorData[i]['courseTitle'],
                    courseCode: errorData[i]['courseCode'],
                    remainingPlaces: errorData[i]['remainingPlaces']
                  },
                  'cursus'
                ) +
                '</div>'
            }

            this.$uibModal.open({
              template: simpleModalTemplate,
              controller: 'SimpleModalCtrl',
              controllerAs: 'smc',
              resolve: {
                title: () => { return title },
                content: () => { return content }
              }
            })
          }
        }
      })
    } else if (this.sourceType === 'user') {
      route = Routing.generate(
        'api_post_users_register_to_multiple_cursus',
        {
          usersIdsTxt: this.sourceId,
          cursusIdsTxt: this.cursusIdsTxt,
          sessionsIdsTxt: sessionsIdsTxt
        }
      )
      this.$http.post(route).then(data => {
        if (data['status'] === 200) {
          this.closeModal()
          const resultsData = data['data']

          if (resultsData['status'] === 'success') {
            this.$state.transitionTo(
              'registration_cursus_management',
              {cursusId: this.cursusId},
              { reload: true, inherit: true, notify: true }
            )
          } else {
            const title = Translator.trans('registration_failed', {}, 'cursus')
            let content = ''
            const errorData = resultsData['datas']

            for (let i = 0; i < errorData.length; i++) {
              content += '<div class="alert alert-danger">' +
                Translator.trans(
                  'session_not_enough_place_msg',
                  {
                    sessionName: errorData[i]['sessionName'],
                    courseTitle: errorData[i]['courseTitle'],
                    courseCode: errorData[i]['courseCode'],
                    remainingPlaces: errorData[i]['remainingPlaces']
                  },
                  'cursus'
                ) +
                '</div>'
            }

            this.$uibModal.open({
              template: simpleModalTemplate,
              controller: 'SimpleModalCtrl',
              controllerAs: 'smc',
              resolve: {
                title: () => { return title },
                content: () => { return content }
              }
            })
          }
        }
      })
    }
  }

  initialize() {
    const route = Routing.generate('api_get_sessions_for_cursus_list', {cursusIdsTxt: this.cursusIdsTxt})
    this.$http.get(route).then(data => {
      if (data['status'] === 200) {
        this.sessionsData = data['data']

        for (let courseId in this.sessionsData) {
          this.selectedSessions[courseId] = 0
          this.sessionsData[courseId]['sessions'].forEach(s => {
            if (s['sessionDefault']) {
              this.selectedSessions[courseId] = s['sessionId']
            }
          })
        }
      }
    })
  }
}