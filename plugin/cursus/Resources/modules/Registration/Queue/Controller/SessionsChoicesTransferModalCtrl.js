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

export default class SessionsChoicesTransferModalCtrl {
  constructor($http, $uibModal, $uibModalInstance, queueId, courseId, callback) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.$uibModalInstance = $uibModalInstance
    this.queueId = queueId
    this.courseId = courseId
    this.callback = callback
    this.sessions = []
    this.selectedSession = null
    this.errorMessage = ''

    this.columns = [
      {
        name: 'checkboxes',
        headerRenderer: () => {
          return '<b></b>'
        },
        cellRenderer: scope => {
          return `
            <span>
                <input type="radio"
                       name="session-selection"
                       value="${scope.$row['id']}"
                       ng-model="sctmc.selectedSession"
                >
            </span>
          `
        }
      },
      {
        name: 'name',
        prop: 'name',
        headerRenderer: () => {
          return `<b>${Translator.trans('name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'sessionStatus',
        headerRenderer: () => {
          return `<b>${Translator.trans('status', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const status = scope.$row['sessionStatus']
          let cell = '<span>'

          if (status === 0) {
            cell += Translator.trans('session_not_started', {}, 'cursus')
          } else if (status === 1) {
            cell += Translator.trans('session_open', {}, 'cursus')
          } else if (status === 2) {
            cell += Translator.trans('session_closed', {}, 'cursus')
          }
          cell += '</span>'

          return cell
        }
      }
    ]

    this.dataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      resizable: true,
      columns: this.columns
    }

    this.getAvailableSessions()
  }

  closeModal() {
    this.$uibModalInstance.close()
  }

  confirmModal() {
    if (this.selectedSession) {
      const route = Routing.generate('api_post_course_queued_user_transfer', {'queue': this.queueId, session: this.selectedSession})
      this.$http.post(route).then(datas => {
        if (datas['status'] === 200) {
          const results = datas['data']

          if (results['status'] === 'success') {
            this.callback(this.courseId, this.queueId)
            this.closeModal()
          } else {
            this.errorMessage = Translator.trans(
              'session_not_enough_place_msg',
              {
                sessionName: results['datas']['sessionName'],
                courseTitle: results['datas']['courseTitle'],
                courseCode: results['datas']['courseCode'],
                remainingPlaces: results['datas']['remainingPlaces']
              },
              'cursus'
            )
          }
        }
      })
    }
  }

  deleteErrorMessage() {
    this.errorMessage = ''
  }

  getAvailableSessions() {
    const route = Routing.generate('api_get_available_sessions_by_course', {course: this.courseId})
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.sessions = datas['data']
      }
    })
  }
}