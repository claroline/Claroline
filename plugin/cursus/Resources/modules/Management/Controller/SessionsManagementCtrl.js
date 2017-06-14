/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
import coursesListTemplate from '../Partial/session_creation_courses_list.html'

export default class SessionsManagementCtrl {
  constructor($uibModal, NgTableParams, SessionService, SessionEventService) {
    this.$uibModal = $uibModal
    this.SessionService = SessionService
    this.SessionEventService = SessionEventService
    this.sessions = SessionService.getSessions()
    this.events = SessionEventService.getOpenSessionEvents()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.sessions}
    )
    this.filterStartDate = {
      date: null,
      format: 'dd/MM/yyyy',
      open: false
    }
    this.filterEndDate = {
      date: null,
      format: 'dd/MM/yyyy',
      open: false
    }
    this.dateOptions = {
      formatYear: 'yy',
      startingDay: 1,
      placeHolder: 'jj/mm/aaaa'
    }
    this.isCollapsed = {}
    this._addSessionCallback = this._addSessionCallback.bind(this)
    this._updateSessionCallback = this._updateSessionCallback.bind(this)
    this._deleteSessionCallback = this._deleteSessionCallback.bind(this)
    this.initialize()
  }

  _addSessionCallback(data) {
    this.SessionService._addSessionCallback(data)
    this.tableParams.reload()
  }

  _updateSessionCallback(data) {
    this.SessionService._updateSessionCallback(data)
    this.tableParams.reload()
  }

  _deleteSessionCallback(data) {
    this.SessionService._removeSessionCallback(data)
    this.tableParams.reload()
  }

  initialize() {
    this.SessionService.loadSessions()
  }

  isInitialized() {
    return this.SessionService.isInitialized()
  }

  loadEvents(sessionId) {
    this.SessionEventService.loadEventsBySession(sessionId)
  }

  editSession(session) {
    this.SessionService.editSession(session, this._updateSessionCallback)
  }

  deleteSession(sessionId) {
    this.SessionService.deleteSession(sessionId, this._deleteSessionCallback)
  }

  createEvent(session) {
    this.SessionService.loadUsersBySession(session['id'])
    this.loadEvents(session['id'])
    this.SessionEventService.createSessionEvent(session)
  }

  openStartDatePicker() {
    this.filterStartDate['open'] = true
  }

  openEndDatePicker() {
    this.filterEndDate['open'] = true
  }

  isValidStartDate(startDate) {
    let isValid = false

    if (startDate) {
      const startTime = new Date(startDate).getTime()
      const filterStartTime = (this.filterStartDate['date'] === null || this.filterStartDate['date'] === undefined) ?
        null :
        this.filterStartDate['date'].getTime()
      const filterEndTime = (this.filterEndDate['date'] === null || this.filterEndDate['date'] === undefined) ?
        null :
        this.filterEndDate['date'].getTime()

      isValid = (filterStartTime === null || startTime >= filterStartTime) && (filterEndTime === null || startTime <= filterEndTime)
    }

    return isValid
  }

  displayCoursesList() {
    this.$uibModal.open({
      template: coursesListTemplate,
      controller: 'SessionCreationCoursesListModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        callback: () => { return this._addSessionCallback }
      }
    })
  }

  sendMessageToSessionLearners(session) {
    this.SessionService.sendMessageToSession(session)
  }

  manageEventComments(sessionEvent) {
    this.SessionEventService.manageComments(sessionEvent)
  }

  openWorkspace(workspaceId) {
    window.location = Routing.generate('claro_workspace_open', {workspaceId: workspaceId})
  }
}