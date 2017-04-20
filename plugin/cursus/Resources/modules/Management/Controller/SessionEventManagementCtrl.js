/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class SessionEventManagementCtrl {
  constructor($stateParams, NgTableParams, CourseService, SessionService, SessionEventService, DocumentModelService) {
    this.NgTableParams = NgTableParams
    this.CourseService = CourseService
    this.SessionService = SessionService
    this.SessionEventService = SessionEventService
    this.DocumentModelService = DocumentModelService
    this.sessionId = $stateParams.sessionId
    this.sessionEventId = $stateParams.sessionEventId
    this.sessionEvent = SessionEventService.getSessionEvent()
    this.users = SessionEventService.getUsersBySessionEvent(this.sessionEventId)
    this.breadCrumbLabel = ''
    this.breadCrumbLabelEvent = ''
    this.isCollapsed = {
      description: true,
      users: false
    }
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.users}
    )
    this.isSessionEventRegistrationDisabled = true
    this.isCertificatesDisabled = true
    this.isInvitationsDisabled = true
    this._updateSessionEventCallback = this._updateSessionEventCallback.bind(this)
    this._addUsersCallback = this._addUsersCallback.bind(this)
    this._removeUserCallback = this._removeUserCallback.bind(this)
    this._updateTableParamsCallback = this._updateTableParamsCallback.bind(this)
    this.initialize()
  }

  _updateSessionEventCallback(data) {
    this.SessionEventService._updateSessionEventCallback(data)
    const sessionEventJson = JSON.parse(data)
    this.sessionEvent = sessionEventJson
    this.breadCrumbLabelEvent = sessionEventJson['name']
    this.SessionEventService.forceLoadUsersBySessionEvent(this.sessionEventId, this._updateTableParamsCallback)
  }

  _addUsersCallback(data) {
    this.SessionEventService._addUsersCallback(data)
    this.tableParams.reload()
  }

  _removeUserCallback(data) {
    this.SessionEventService._removeUserCallback(data)
    this.tableParams.reload()
  }

  _updateTableParamsCallback() {
    this.tableParams.reload()
  }

  initialize() {
    let result = this.SessionEventService.getSessionEventById(this.sessionId, this.sessionEventId)

    if (result === 'initialized') {
      this.breadCrumbLabelEvent = this.sessionEvent['name']
      this.breadCrumbLabel = this.sessionEvent['session']['name']
    } else {
      result.then(d => {
        if (d === 'initialized' && this.sessionEvent) {
          this.breadCrumbLabelEvent = this.sessionEvent['name']
          this.breadCrumbLabel = this.sessionEvent['session']['name']
        }
      })
    }
    this.CourseService.getGeneralParameters().then(d => {
      this.isSessionEventRegistrationDisabled = d['disableSessionEventRegistration']
      this.isCertificatesDisabled = d['disableCertificates']
      this.isInvitationsDisabled = d['disableInvitations']
    })
    this.SessionEventService.loadEventsBySession(this.sessionId)
    this.SessionService.loadUsersBySession(this.sessionId)
    this.SessionEventService.loadUsersBySessionEvent(this.sessionEventId)
  }

  editSessionEvent() {
    this.SessionEventService.editSessionEvent(this.sessionEvent, this._updateSessionEventCallback)
  }

  registerParticipants() {
    this.SessionEventService.registerParticipants(this.sessionId, this.sessionEventId, this._addUsersCallback)
  }

  deleteParticipant(sessionEventUserId) {
    this.SessionEventService.deleteParticipant(sessionEventUserId, this._removeUserCallback)
  }

  manageEventComments() {
    this.SessionEventService.manageComments(this.sessionEvent)
  }

  inviteLearnersToEvent() {
    this.DocumentModelService.displayDocumentSelection(this.sessionEvent, 1)
  }

  generateEventCertificates() {
    this.DocumentModelService.displayDocumentSelection(this.sessionEvent, 3)
  }

  exportUsers() {
    this.SessionEventService.exportUsersForm(this.sessionEventId)
  }
}