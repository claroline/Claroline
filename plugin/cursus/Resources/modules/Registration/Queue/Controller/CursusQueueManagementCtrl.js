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
import simpleModalTemplate from '../../Cursus/Partial/simple_modal.html'
import sessionsChoicesTransferTemplate from '../Partial/sessions_choices_transfer_modal.html'

export default class CursusQueueManagementCtrl {
  constructor($http, $uibModal) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.connectedUser = {id: 0}
    this.courses = []
    this.coursesQueues = []
    this.sessionsQueues = []
    this.search = ''
    this.tempSearch = ''
    this.isAdmin = false

    this.coursesColumns = [
      {
        name: 'firstName',
        prop: 'firstName',
        headerRenderer: () => {
          return `<b>${Translator.trans('first_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'lastName',
        prop: 'lastName',
        headerRenderer: () => {
          return `<b>${Translator.trans('last_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'applicationDate',
        prop: 'applicationDate',
        headerRenderer: () => {
          return `<b>${Translator.trans('application_date', {}, 'cursus')}</b>`
        },
        cellRenderer: scope => {
          return `<span>${scope.$row['applicationDate']}</span>`
        }
      },
      {
        name: 'sessionName',
        prop: 'sessionName',
        headerRenderer: () => {
          return `<b>${Translator.trans('session', {}, 'cursus')}</b>`
        },
        cellRenderer: () => {
          return `<span>[${Translator.trans('to_define', {}, 'cursus')}]</span>`
        }
      },
      {
        name: 'status',
        prop: 'status',
        headerRenderer: () => {
          return `<b>${Translator.trans('status', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const status = scope.$row['status']
          const userValidation = (status & 2) === 2
          const validatorValidation = (status & 4) === 4
          const organizationValidation = (status & 8) === 8
          let cell = ''

          if (status === 1) {
            cell += `
              <span class="label label-warning"
                    data-toggle="tooltip"
                    data-placement="top"
                    data-container="body"
                    data-title="${Translator.trans('waiting_for_session_transfer', {}, 'cursus')}"
              >
                  <i class="fa fa-clock-o"></i>
                  &nbsp;
                  ${Translator.trans('session', {}, 'cursus')}
              </span>
              <br>
            `
          } else {
            if (userValidation) {
              cell += `
                <span class="label label-primary"
                      data-toggle="tooltip"
                      data-placement="top"
                      data-container="body"
                      data-title="${Translator.trans('waiting_user_validation', {}, 'cursus')}"
                >
                    <i class="fa fa-clock-o"></i>
                    &nbsp;
                    ${Translator.trans('user', {}, 'platform')}
                </span>
                <br>
              `
            }

            if (organizationValidation) {
              cell += `
                <span class="label label-danger"
                      data-toggle="tooltip"
                      data-placement="top"
                      data-container="body"
                      data-title="${Translator.trans('waiting_organization_validation', {}, 'cursus')}"
                >
                    <i class="fa fa-clock-o"></i>
                    &nbsp;
                    ${Translator.trans('organization', {}, 'platform')}
                </span>
                <br>
              `
            }

            if (validatorValidation) {
              cell += `
                <span class="label label-success"
                      data-toggle="tooltip"
                      data-placement="top"
                      data-container="body"
                      data-title="${Translator.trans('waiting_validator_validation', {}, 'cursus')}"
                >
                    <i class="fa fa-clock-o"></i>
                    &nbsp;
                    ${Translator.trans('validator', {}, 'cursus')}
                </span>
                <br>
              `
            }
          }

          return cell
        }
      },
      {
        name: 'actions',
        headerRenderer: () => {
          return `<b>${Translator.trans('actions', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const rights = scope.$row['rights']
          const status = scope.$row['status']
          const isValidated = (status === 1)
          const userValidation = (status & 2) === 2
          const validatorValidation = (status & 4) === 4
          const organizationValidation = (status & 8) === 8
          const isValidator = (rights & 4) === 4
          const isOrganizationAdmin = (rights & 8) === 8

          const disabled = !this.isAdmin && (
            userValidation ||
            (organizationValidation && !isOrganizationAdmin) ||
            (!organizationValidation && validatorValidation && !isValidator)
          )
          let disabledMsg = ''

          if (userValidation) {
            disabledMsg = Translator.trans('waiting_user_validation', {}, 'cursus')
          } else if (organizationValidation && !isOrganizationAdmin) {
            disabledMsg = Translator.trans('waiting_organization_validation', {}, 'cursus')
          }
          let cell = ''

          if (isValidated) {
            cell += `
              <button class="btn btn-success btn-sm"
                      ng-click="cqmc.transferCourseQueue(${scope.$row['id']}, ${scope.$row['courseId']})"
              >
                  <i class="fa fa-sign-in"></i>
              </button>
              &nbsp;
            `
          } else {
            cell += disabled ?
              `
                <button class="btn btn-success btn-sm disabled"
                        data-toggle="tooltip"
                        data-placement="top"
                        data-container="body"
                        data-title="${disabledMsg}"
                >
              ` :
              `
                <button class="btn btn-success btn-sm"
                        ng-click="cqmc.validateCourseQueue(${scope.$row['id']})"
                >
              `
            cell += `
                  <i class="fa fa-check"></i>
              </button>
              &nbsp;
            `
          }
          cell += `
            <button class="btn btn-danger btn-sm"
                    ng-click="cqmc.declineCourseQueue(${scope.$row['id']})"
            >
                <i class="fa fa-times"></i>
            </button>
          `

          return cell
        }
      }
    ]

    this.sessionsColumns = [
      {
        name: 'firstName',
        prop: 'firstName',
        headerRenderer: () => {
          return `<b>${Translator.trans('first_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'lastName',
        prop: 'lastName',
        headerRenderer: () => {
          return `<b>${Translator.trans('last_name', {}, 'platform')}</b>`
        }
      },
      {
        name: 'applicationDate',
        prop: 'applicationDate',
        headerRenderer: () => {
          return `<b>${Translator.trans('application_date', {}, 'cursus')}</b>`
        },
        cellRenderer: scope => {
          return `<span>${scope.$row['applicationDate']}</span>`
        }
      },
      {
        name: 'sessionName',
        prop: 'sessionName',
        headerRenderer: () => {
          return `<b>${Translator.trans('session', {}, 'cursus')}</b>`
        }
      },
      {
        name: 'status',
        prop: 'status',
        headerRenderer: () => {
          return `<b>${Translator.trans('status', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const status = scope.$row['status']
          const userValidation = (status & 2) === 2
          const validatorValidation = (status & 4) === 4
          const organizationValidation = (status & 8) === 8
          let cell = '<span>'

          if (userValidation) {
            cell += `
              <span class="label label-primary"
                    data-toggle="tooltip"
                    data-placement="top"
                    data-container="body"
                    data-title="${Translator.trans('waiting_user_validation', {}, 'cursus')}"
              >
                  <i class="fa fa-clock-o"></i>
                  &nbsp;
                  ${Translator.trans('user', {}, 'platform')}
              </span>
              <br>
            `
          }

          if (organizationValidation) {
            cell += `
              <span class="label label-danger"
                    data-toggle="tooltip"
                    data-placement="top"
                    data-container="body"
                    data-title="${Translator.trans('waiting_organization_validation', {}, 'cursus')}"
              >
                  <i class="fa fa-clock-o"></i>
                  &nbsp;
                  ${Translator.trans('organization', {}, 'platform')}
              </span>
              <br>
            `
          }

          if (validatorValidation) {
            cell += `
              <span class="label label-success"
                    data-toggle="tooltip"
                    data-placement="top"
                    data-container="body"
                    data-title="${Translator.trans('waiting_validator_validation', {}, 'cursus')}"
              >
                  <i class="fa fa-clock-o"></i>
                  &nbsp;
                  ${Translator.trans('validator', {}, 'cursus')}
              </span>
              <br>
            `
          }
          cell += '</span>'

          return cell
        }
      },
      {
        name: 'actions',
        headerRenderer: () => {
          return `<b>${Translator.trans('actions', {}, 'platform')}</b>`
        },
        cellRenderer: scope => {
          const rights = scope.$row['rights']
          const status = scope.$row['status']
          const userValidation = (status & 2) === 2
          const validatorValidation = (status & 4) === 4
          const organizationValidation = (status & 8) === 8
          const isValidator = (rights & 4) === 4
          const isOrganizationAdmin = (rights & 8) === 8

          const disabled = !this.isAdmin && (
            userValidation ||
            (organizationValidation && !isOrganizationAdmin) ||
            (!organizationValidation && validatorValidation && !isValidator)
          )
          let disabledMsg = ''

          if (userValidation) {
            disabledMsg = Translator.trans('waiting_user_validation', {}, 'cursus')
          } else if (organizationValidation && !isOrganizationAdmin) {
            disabledMsg = Translator.trans('waiting_organization_validation', {}, 'cursus')
          }

          let cell = disabled ?
            `
              <button class="btn btn-success btn-sm disabled"
                      data-toggle="tooltip"
                      data-placement="top"
                      data-container="body"
                      data-title="${disabledMsg}"
              >
            ` :
            `
              <button class="btn btn-success btn-sm"
                      ng-click="cqmc.validateSessionQueue(${scope.$row['id']})"
              >
            `
          cell += `
                <i class="fa fa-check"></i>
            </button>
            &nbsp;
            <button class="btn btn-danger btn-sm"
                    ng-click="cqmc.declineSessionQueue(${scope.$row['id']})"
            >
                <i class="fa fa-times"></i>
            </button>
          `

          return cell
        }
      }
    ]

    this.coursesDataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      resizable: true,
      columns: this.coursesColumns
    }

    this.sessionsDataTableOptions = {
      scrollbarV: false,
      columnMode: 'force',
      headerHeight: 50,
      selectable: true,
      multiSelect: true,
      checkboxSelection: true,
      resizable: true,
      columns: this.sessionsColumns
    }

    this.removeCourseQueue = this.removeCourseQueue.bind(this)
    this.initialize()
  }

  searchDatas() {
    this.search = this.tempSearch

    if (this.search === '') {
      this.getAllDatas()
    } else {
      this.getSearchedDatas(this.search)
    }
  }

  declineCourseQueue(queueId) {
    const route = Routing.generate('api_delete_course_queue', {queue: queueId})
    this.$http.delete(route).then(
      datas => {
        if (datas['status'] === 200) {
          const queueDatas = datas['data']
          this.removeCourseQueue(queueDatas['courseId'], queueDatas['id'])
        }
      },
      datas => {
        if (datas['status'] === 403) {
          const title = Translator.trans('decline_failed', {}, 'cursus')
          const content = '<div class="alert alert-danger">' +
            Translator.trans(datas['data'], {}, 'cursus') +
            '</div>'

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
    )
  }

  declineSessionQueue(queueId) {
    const route = Routing.generate('api_delete_session_queue', {queue: queueId})
    this.$http.delete(route).then(
      datas => {
        if (datas['status'] === 200) {
          const queueDatas = datas['data']
          this.removeSessionQueue(queueDatas['courseId'], queueDatas['id'])
        }
      },
      datas => {
        if (datas['status'] === 403) {
          const title = Translator.trans('decline_failed', {}, 'cursus')
          const content = '<div class="alert alert-danger">' +
            Translator.trans(datas['data'], {}, 'cursus') +
            '</div>'

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
    )
  }

  validateCourseQueue(queueId) {
    const route = Routing.generate('api_put_course_queue_validate', {queue: queueId})
    this.$http.put(route).then(
      datas => {
        if (datas['status'] === 200) {
          const queueDatas = datas['data']

          if (queueDatas['type'] === 'not_authorized') {
            const title = Translator.trans('validation_failed', {}, 'cursus')
            const content = `
              <div class="alert alert-danger">
                  ${Translator.trans('not_authorized', {}, 'cursus')}
              </div>
            `

            this.$uibModal.open({
              template: simpleModalTemplate,
              controller: 'SimpleModalCtrl',
              controllerAs: 'smc',
              resolve: {
                title: () => { return title },
                content: () => { return content }
              }
            })
          } else if (queueDatas['type'] === 'admin_validated') {
            this.updateCourseQueue(
              queueDatas['courseId'],
              queueDatas['id'],
              queueDatas['queueStatus'],
              0
            )
          } else if (queueDatas['type'] === 'organization_validated') {
            this.updateCourseQueue(
              queueDatas['courseId'],
              queueDatas['id'],
              queueDatas['queueStatus'],
              8
            )
          } else if (queueDatas['type'] === 'validator_validated') {
            this.updateCourseQueue(
              queueDatas['courseId'],
              queueDatas['id'],
              queueDatas['queueStatus'],
              4
            )
          }
        }
      }
    )
  }

  validateSessionQueue(queueId) {
    const route = Routing.generate('api_put_session_queue_validate', {queue: queueId})
    this.$http.put(route).then(
      datas => {
        if (datas['status'] === 200) {
          const queueDatas = datas['data']

          if (queueDatas['type'] === 'not_authorized') {
            const title = Translator.trans('validation_failed', {}, 'cursus')
            const content = `
              <div class="alert alert-danger">
                  ${Translator.trans('not_authorized', {}, 'cursus')}
              </div>
            `

            this.$uibModal.open({
              template: simpleModalTemplate,
              controller: 'SimpleModalCtrl',
              controllerAs: 'smc',
              resolve: {
                title: () => { return title },
                content: () => { return content }
              }
            })
          } else {
            if (queueDatas['type'] === 'organization_validated') {
              this.updateSessionQueue(
                queueDatas['courseId'],
                queueDatas['id'],
                queueDatas['queueStatus'],
                8
              )
            } else if (queueDatas['type'] === 'validator_validated') {
              this.updateSessionQueue(
                queueDatas['courseId'],
                queueDatas['id'],
                queueDatas['queueStatus'],
                4
              )
            } else if (queueDatas['type'] === 'registered') {
              this.removeSessionQueue(queueDatas['courseId'], queueDatas['id'])
            }

            if (queueDatas['status'] === 'failed') {
              const title = Translator.trans('registration_failed', {}, 'cursus')
              const content = `
                <div class="alert alert-danger">
                    ${Translator.trans('full_session_msg', {}, 'cursus')}
                </div>
              `

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
        }
      }
    )
  }

  transferCourseQueue(queueId, courseId) {
    this.$uibModal.open({
      template: sessionsChoicesTransferTemplate,
      controller: 'SessionsChoicesTransferModalCtrl',
      controllerAs: 'sctmc',
      resolve: {
        queueId: () => { return queueId },
        courseId: () => { return courseId },
        callback: () => { return this.removeCourseQueue }
      }
    })
  }

  getAllDatas() {
    const route = Routing.generate('api_get_registration_queues_datas')
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.courses = datas['data']['courses']
        this.coursesQueues = datas['data']['coursesQueues']
        this.sessionsQueues = datas['data']['sessionsQueues']
      }
    })
  }

  getSearchedDatas(search) {
    const route = Routing.generate('api_get_registration_queues_datas_by_search', {search: search})
    this.$http.get(route).then(datas => {
      if (datas['status'] === 200) {
        this.courses = datas['data']['courses']
        this.coursesQueues = datas['data']['coursesQueues']
        this.sessionsQueues = datas['data']['sessionsQueues']
      }
    })
  }

  checkAdminRole() {
    if (this.connectedUser['roles']) {
      const roles = this.connectedUser['roles']

      for (let i = 0; i < roles.length; i++) {
        if (roles[i]['name'] === 'ROLE_ADMIN') {
          this.isAdmin = true
          break
        }
      }
    }
  }

  removeCourseQueue(courseId, queueId) {
    if (this.coursesQueues[courseId]) {
      for (let i = 0; i < this.coursesQueues[courseId].length; i++) {
        const queueDatas = this.coursesQueues[courseId][i]

        if (queueDatas['id'] === queueId) {
          this.coursesQueues[courseId].splice(i, 1)
          break
        }
      }
    }
  }

  removeSessionQueue(courseId, queueId) {
    if (this.sessionsQueues[courseId]) {
      for (let i = 0; i < this.sessionsQueues[courseId].length; i++) {
        const queueDatas = this.sessionsQueues[courseId][i]

        if (queueDatas['id'] === queueId) {
          this.sessionsQueues[courseId].splice(i, 1)
          break
        }
      }
    }
  }

  updateCourseQueue(courseId, queueId, status, mask) {
    if (this.coursesQueues[courseId]) {
      for (let i = 0; i < this.coursesQueues[courseId].length; i++) {
        const queueDatas = this.coursesQueues[courseId][i]

        if (queueDatas['id'] === queueId) {
          this.coursesQueues[courseId][i]['status'] = status
          let rights = this.coursesQueues[courseId][i]['rights']

          if ((rights & mask) === mask) {
            rights -= mask
            this.coursesQueues[courseId][i]['rights'] = rights

            if (status !== 1 && rights === 0) {
              this.removeCourseQueue(courseId, queueId)
            }
          }
          break
        }
      }
    }
  }

  updateSessionQueue(courseId, queueId, status, mask) {
    if (this.sessionsQueues[courseId]) {
      for (let i = 0; i < this.sessionsQueues[courseId].length; i++) {
        const queueDatas = this.sessionsQueues[courseId][i]

        if (queueDatas['id'] === queueId) {
          this.sessionsQueues[courseId][i]['status'] = status
          let rights = this.sessionsQueues[courseId][i]['rights']

          if ((rights & mask) === mask) {
            rights -= mask
            this.sessionsQueues[courseId][i]['rights'] = rights

            if (status !== 1 && rights === 0) {
              this.removeSessionQueue(courseId, queueId)
            }
          }
          break
        }
      }
    }
  }

  initialize() {
    const userRoute = Routing.generate('api_get_connected_user')
    this.$http.get(userRoute).then(userDatas => {
      if (userDatas['status'] === 200) {
        this.connectedUser = userDatas['data']
        this.checkAdminRole()

        const coursesRoute = Routing.generate('api_get_registration_queues_datas')
        this.$http.get(coursesRoute).then(coursesDatas => {
          if (coursesDatas['status'] === 200) {
            this.courses = coursesDatas['data']['courses']
            this.coursesQueues = coursesDatas['data']['coursesQueues']
            this.sessionsQueues = coursesDatas['data']['sessionsQueues']
          }
        })
      }
    })
  }
}