/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class SessionManagementCtrl {
  constructor($stateParams, NgTableParams, CourseService, SessionService, SessionEventService, DocumentModelService) {
    this.NgTableParams = NgTableParams
    this.CourseService = CourseService
    this.SessionService = SessionService
    this.SessionEventService = SessionEventService
    this.DocumentModelService = DocumentModelService
    this.sessionId = $stateParams.sessionId
    this.session = SessionService.getSession()
    this.openEvents = SessionEventService.getOpenSessionEventsBySession(this.sessionId)
    this.closedEvents = SessionEventService.getClosedSessionEventsBySession(this.sessionId)
    this.groups = []
    this.learners = SessionService.getLearnersBySession(this.sessionId)
    this.tutors = SessionService.getTutorsBySession(this.sessionId)
    this.pendingLearners = SessionService.getPendingLearnersBySession(this.sessionId)
    this.breadCrumbLabel = ''
    this.isCollapsed = {
      description: true,
      learners: false,
      tutors: false,
      openEvents: false,
      closedEvents: false
    }
    this.tableParams = {
      groups: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.groups}
      ),
      learners: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.learners}
      ),
      tutors: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.tutors}
      ),
      pendingLearners: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.pendingLearners}
      ),
      openEvents: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.openEvents}
      ),
      closedEvents: new NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.closedEvents}
      )
    }
    this.isCertificatesDisabled = true
    this.isInvitationsDisabled = true
    this._updateSessionCallback = this._updateSessionCallback.bind(this)
    this._addSessionEventCallback = this._addSessionEventCallback.bind(this)
    this._addMultipleSessionEventsCallback = this._addMultipleSessionEventsCallback.bind(this)
    this._updateSessionEventCallback = this._updateSessionEventCallback.bind(this)
    this._removeSessionEventCallback = this._removeSessionEventCallback.bind(this)
    this._removeLearnerCallback = this._removeLearnerCallback.bind(this)
    this._removeTutorCallback = this._removeTutorCallback.bind(this)
    this._removeGroupCallback = this._removeGroupCallback.bind(this)
    this._removePendingLearnerCallback = this._removePendingLearnerCallback.bind(this)
    this._initializeGroupsCallback = this._initializeGroupsCallback.bind(this)
    this._acceptQueueCallback = this._acceptQueueCallback.bind(this)
    this._addLearnersCallback = this._addLearnersCallback.bind(this)
    this._addLearnersGroupsCallback = this._addLearnersGroupsCallback.bind(this)
    this._addTutorsCallback = this._addTutorsCallback.bind(this)
    this.initialize()
  }

  _updateSessionCallback(data) {
    this.SessionService._updateSessionCallback(data)
    const sessionJson = JSON.parse(data)
    this.session = sessionJson
    this.breadCrumbLabel = sessionJson['name']
  }

  _addSessionEventCallback(data) {
    this.SessionEventService._addSessionEventCallback(data)
    this.refreshEventsTables()
  }

  _addMultipleSessionEventsCallback(data) {
    this.SessionEventService._addMultipleSessionEventsCallback(data)
    this.refreshEventsTables()
  }

  _updateSessionEventCallback(data) {
    this.SessionEventService._updateSessionEventCallback(data)
    this.refreshEventsTables()
  }

  _removeSessionEventCallback(data) {
    this.SessionEventService._removeSessionEventCallback(data)
    this.refreshEventsTables()
  }

  _removeLearnerCallback(data) {
    this.SessionService._removeLearnerCallback(data)
    this.tableParams['learners'].reload()
  }

  _removeTutorCallback(data) {
    this.SessionService._removeTutorCallback(data)
    this.tableParams['tutors'].reload()
  }

  _removeGroupCallback(data) {
    this.SessionService._removeGroupCallback(data)
    this.tableParams['groups'].reload()
    this.tableParams['learners'].reload()
  }

  _removePendingLearnerCallback(data) {
    this.SessionService._removePendingLearnerCallback(data)
    this.tableParams['pendingLearners'].reload()
  }

  _initializeGroupsCallback() {
    this.groups = this.SessionService.getGroupsBySession(this.sessionId)

    this.tableParams['groups'] = new this.NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.groups}
    )
  }

  _acceptQueueCallback(data) {
    this.SessionService._acceptQueueCallback(data)
    this.tableParams['learners'].reload()
    this.tableParams['pendingLearners'].reload()
  }

  _addLearnersCallback(data) {
    this.SessionService._addLearnersCallback(data)
    this.tableParams['learners'].reload()
  }

  _addLearnersGroupsCallback(groupData, usersData) {
    this.SessionService._addLearnersGroupsCallback(groupData, usersData)
    this.tableParams['groups'].reload()
    this.tableParams['learners'].reload()
  }

  _addTutorsCallback(data) {
    this.SessionService._addTutorsCallback(data)
    this.tableParams['tutors'].reload()
  }

  initialize() {
    let result = this.SessionService.getSessionById(this.sessionId)

    if (result === 'initialized') {
      this.breadCrumbLabel = this.session['name']
    } else {
      result.then(d => {
        if (d === 'initialized' && this.session) {
          this.breadCrumbLabel = this.session['name']
        }
      })
    }
    this.CourseService.getGeneralParameters().then(d => {
      this.isCertificatesDisabled = d['disableCertificates']
      this.isInvitationsDisabled = d['disableInvitations']
    })
    this.SessionService.loadUsersBySession(this.sessionId)
    this.SessionService.loadGroupsBySession(this.sessionId, this._initializeGroupsCallback)
    this.SessionService.loadPendingUsersBySession(this.sessionId)
    this.SessionEventService.loadEventsBySession(this.sessionId)
  }

  createSessionEvent() {
    this.SessionEventService.createSessionEvent(this.session, this._addSessionEventCallback)
  }

  editSession() {
    this.SessionService.editSession(this.session, this._updateSessionCallback)
  }

  editEvent(event) {
    this.SessionEventService.editSessionEvent(event, this._updateSessionEventCallback)
  }

  deleteEvent(eventId) {
    this.SessionEventService.deleteSessionEvent(eventId, this._removeSessionEventCallback)
  }

  repeatEvent(sessionEvent) {
    this.SessionEventService.repeatEvent(sessionEvent, this._addMultipleSessionEventsCallback)
  }

  refreshEventsTables() {
    this.tableParams['openEvents'].reload()
    this.tableParams['closedEvents'].reload()
  }

  deleteTutor(sessionUserId) {
    this.SessionService.deleteTutor(sessionUserId, this._removeTutorCallback)
  }

  deleteLearner(sessionUserId) {
    this.SessionService.deleteLearner(sessionUserId, this._removeLearnerCallback)
  }

  deleteGroup(sessionGroupId) {
    this.SessionService.deleteGroup(sessionGroupId, this._removeGroupCallback)
  }

  sendConfirmationMail(userId) {
    this.SessionService.sendConfirmationMail(this.sessionId, userId)
  }

  acceptQueue(queueId) {
    this.SessionService.acceptQueue(queueId, this._acceptQueueCallback)
  }

  declineQueue(queueId) {
    this.SessionService.declineQueue(queueId, this._removePendingLearnerCallback)
  }

  registerLearners($event) {
    $event.preventDefault()
    $event.stopPropagation()
    this.SessionService.registerLearners(this.sessionId, this._addLearnersCallback)
  }

  registerLearnersGroups($event) {
    $event.preventDefault()
    $event.stopPropagation()
    this.SessionService.registerLearnersGroups(this.sessionId, this._addLearnersGroupsCallback)
  }

  registerTutors($event) {
    $event.preventDefault()
    $event.stopPropagation()
    this.SessionService.registerTutors(this.sessionId, this._addTutorsCallback)
  }

  manageEventComments(sessionEvent) {
    this.SessionEventService.manageComments(sessionEvent)
  }

  inviteLearners() {
    this.DocumentModelService.displayDocumentSelection(this.session, 0)
  }

  generateCertificates() {
    this.DocumentModelService.displayDocumentSelection(this.session, 2)
  }

  generateEventCertificates(sessionEvent) {
    this.DocumentModelService.displayDocumentSelection(sessionEvent, 3)
  }

  inviteLearnersToEvent(sessionEvent) {
    this.DocumentModelService.displayDocumentSelection(sessionEvent, 1)
  }

  sendMessageToSessionLearners() {
    this.SessionService.sendMessageToSession(this.session)
  }

  exportUsers() {
    this.SessionService.exportUsersForm(this.sessionId)
  }

  openWorkspace() {
    window.location = Routing.generate('claro_workspace_open', {workspaceId: this.session['workspace']['id']})
  }
}