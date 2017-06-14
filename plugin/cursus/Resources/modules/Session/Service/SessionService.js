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
import angular from 'angular/index'
import sessionDeleteTemplate from '../Partial/session_delete_modal.html'
import learnersRegistrationTemplate from '../Partial/learners_registration_modal.html'
import learnersGroupsRegistrationTemplate from '../Partial/learners_groups_registration_modal.html'
import tutorsRegistrationTemplate from '../Partial/tutors_registration_modal.html'
import sessionFormTemplate from '../Partial/session_form_modal.html'
import sessionMessageTemplate from '../Partial/session_message_modal.html'
import sessionUsersExportTemplate from '../Partial/session_users_export_modal.html'

export default class SessionService {
  constructor($http, $uibModal, ClarolineAPIService, CourseService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.CourseService = CourseService
    this.initialized = false
    this.pendingInitialized = {}
    this.session = {}
    this.sessions = []
    this.users = {}
    this.groups = {}
    this.learners = {}
    this.tutors = {}
    this.pendingLearners = {}
    this.courseSessions = {}
    this.openCourseSessions = {}
    this.closedCourseSessions = {}
    this._addSessionCallback = this._addSessionCallback.bind(this)
    this._updateSessionCallback = this._updateSessionCallback.bind(this)
    this._removeSessionCallback = this._removeSessionCallback.bind(this)
    this._resetDefaultSessionCallback = this._resetDefaultSessionCallback.bind(this)
    this._removeLearnerCallback = this._removeLearnerCallback.bind(this)
    this._removeTutorCallback = this._removeTutorCallback.bind(this)
    this._removeGroupCallback = this._removeGroupCallback.bind(this)
    this._removePendingLearnerCallback = this._removePendingLearnerCallback.bind(this)
    this._acceptQueueCallback = this._acceptQueueCallback.bind(this)
    this._addLearnersCallback = this._addLearnersCallback.bind(this)
    this._addLearnersGroupsCallback = this._addLearnersGroupsCallback.bind(this)
    this._addTutorsCallback = this._addTutorsCallback.bind(this)
  }

  _addSessionCallback(data) {
    const sessionsJson = JSON.parse(data)

    if (Array.isArray(sessionsJson)) {
      sessionsJson.forEach(s => {
        if (s['course']['id']) {
          const courseId = s['course']['id']
          s['course_title'] = s['course']['title']
          s['course_code'] = s['course']['code']

          if (s['defaultSession']) {
            this._resetDefaultSessionCallback(courseId, s['id'])
          }

          if (this.courseSessions[courseId] === undefined) {
            this.courseSessions[courseId] = []
          }
          this.courseSessions[courseId].push(s)
          this.computeSessionsStatusByCourse(courseId)
        }
        this.sessions.push(s)
      })
    } else {
      if (sessionsJson['course']['id']) {
        const courseId = sessionsJson['course']['id']
        sessionsJson['course_title'] = sessionsJson['course']['title']
        sessionsJson['course_code'] = sessionsJson['course']['code']

        if (sessionsJson['defaultSession']) {
          this._resetDefaultSessionCallback(courseId, sessionsJson['id'])
        }

        if (this.courseSessions[courseId] === undefined) {
          this.courseSessions[courseId] = []
        }
        this.courseSessions[courseId].push(sessionsJson)
        this.computeSessionsStatusByCourse(courseId)
      }
      this.sessions.push(sessionsJson)
    }
  }

  _updateSessionCallback(data) {
    const sessionJson = JSON.parse(data)
    sessionJson['course_title'] = sessionJson['course']['title']
    sessionJson['course_code'] = sessionJson['course']['code']
    const isDefault = sessionJson['defaultSession']
    const sessionId = sessionJson['id']
    const courseId = sessionJson['course']['id']
    const index = this.sessions.findIndex(s => s['id'] === sessionId)
    const sessionIndex = this.courseSessions[courseId] ?
      this.courseSessions[courseId].findIndex(s => s['id'] === sessionId) :
      -1

    if (isDefault) {
      this.resetDefaultSession(courseId, sessionId)
    }

    if (index > -1) {
      this.sessions[index] = sessionJson
    }

    if (sessionIndex > -1) {
      this.courseSessions[courseId][sessionIndex] = sessionJson
    }
    this.computeSessionsStatusByCourse(courseId)
  }

  _removeSessionCallback(data) {
    const sessionJson = JSON.parse(data)
    const courseId = sessionJson['course']['id']
    this.CourseService.removeFromArray(this.sessions, sessionJson['id'])
    this.CourseService.removeFromArray(this.courseSessions[courseId], sessionJson['id'])
    this.computeSessionsStatusByCourse(courseId)
  }

  _resetDefaultSessionCallback(courseId, sessionId) {
    this.courseSessions[courseId].forEach(s => {
      if (s['defaultSession'] && s['id'] !== sessionId) {
        s['defaultSession'] = false
      }
    })
  }

  _removeLearnerCallback(data) {
    const sessionUser = JSON.parse(data)
    const id = sessionUser['id']
    const sessionId = sessionUser['session']['id']
    this.CourseService.removeFromArray(this.users[sessionId], id)
    this.CourseService.removeFromArray(this.learners[sessionId], id)
  }

  _removeTutorCallback(data) {
    const sessionUser = JSON.parse(data)
    const id = sessionUser['id']
    const sessionId = sessionUser['session']['id']
    this.CourseService.removeFromArray(this.users[sessionId], id)
    this.CourseService.removeFromArray(this.tutors[sessionId], id)
  }

  _removeGroupCallback(data) {
    const sessionGroup = JSON.parse(data['group'])
    const sessionUsers = JSON.parse(data['users'])
    const sessionId = sessionGroup['session']['id']
    this.CourseService.removeFromArray(this.groups[sessionId], sessionGroup['id'])
    sessionUsers.forEach(su => {
      this.CourseService.removeFromArray(this.users[sessionId], su['id'])
      this.CourseService.removeFromArray(this.learners[sessionId], su['id'])
    })
  }

  _removePendingLearnerCallback(data) {
    const pendingLearner = JSON.parse(data)
    const sessionId = pendingLearner['session']['id']
    this.CourseService.removeFromArray(this.pendingLearners[sessionId], pendingLearner['id'])
  }

  _acceptQueueCallback(data) {
    const queue = JSON.parse(data['queue'])
    const sessionUsers = JSON.parse(data['sessionUsers'])
    const sessionId = queue['session']['id']
    this.CourseService.removeFromArray(this.pendingLearners[sessionId], queue['id'])

    sessionUsers.forEach(su => {
      const generatedSU = this.generateUserDatas(su)
      this.users[sessionId].push(generatedSU)
      this.learners[sessionId].push(generatedSU)
    })
  }

  _addLearnersCallback(data) {
    const sessionUsers = JSON.parse(data)
    sessionUsers.forEach(su => {
      const sessionId = su['session']['id']
      const generatedSU = this.generateUserDatas(su)
      this.users[sessionId].push(generatedSU)
      this.learners[sessionId].push(generatedSU)
    })
  }

  _addLearnersGroupsCallback(groupData, usersData) {
    const sessionGroup = JSON.parse(groupData)
    const sessionId = sessionGroup['session']['id']
    const generatedSG = this.generateGroupDatas(sessionGroup)
    this.groups[sessionId].push(generatedSG)

    const sessionUsers = JSON.parse(usersData)
    sessionUsers.forEach(su => {
      const generatedSU = this.generateUserDatas(su)
      this.users[sessionId].push(generatedSU)
      this.learners[sessionId].push(generatedSU)
    })
  }

  _addTutorsCallback(data) {
    const sessionUsers = JSON.parse(data)
    sessionUsers.forEach(su => {
      const sessionId = su['session']['id']
      const generatedSU = this.generateUserDatas(su)
      this.users[sessionId].push(generatedSU)
      this.tutors[sessionId].push(generatedSU)
    })
  }

  isInitialized() {
    return this.initialized
  }

  getSession() {
    return this.session
  }

  getSessions() {
    return this.sessions
  }

  getCourseSessions() {
    return this.courseSessions
  }

  getOpenCourseSessions() {
    return this.openCourseSessions
  }

  getClosedCourseSessions() {
    return this.closedCourseSessions
  }

  getOpenCourseSessionsByCourse(courseId) {
    if (!this.openCourseSessions[courseId]) {
      this.openCourseSessions[courseId] = []
    }

    return this.openCourseSessions[courseId]
  }

  getClosedCourseSessionsByCourse(courseId) {
    if (!this.closedCourseSessions[courseId]) {
      this.closedCourseSessions[courseId] = []
    }

    return this.closedCourseSessions[courseId]
  }

  getGroupsBySession(sessionId) {
    return this.groups[sessionId] ? this.groups[sessionId] : []
  }

  getLearnersBySession(sessionId) {
    if (!this.learners[sessionId]) {
      this.learners[sessionId] = []
    }

    return this.learners[sessionId]
  }

  getTutorsBySession(sessionId) {
    if (!this.tutors[sessionId]) {
      this.tutors[sessionId] = []
    }

    return this.tutors[sessionId]
  }

  getPendingLearnersBySession(sessionId) {
    if (!this.pendingLearners[sessionId]) {
      this.pendingLearners[sessionId] = []
    }

    return this.pendingLearners[sessionId]
  }

  loadSessions(callback = null) {
    if (!this.initialized) {
      this.sessions.splice(0, this.sessions.length)
      const route = Routing.generate('claroline_cursus_all_sessions_retrieve')

      return this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          angular.merge(this.sessions, JSON.parse(d['data']))
          this.computeSessionsStatus()
          this.generateCourseInfos()
          this.generateCourseSessions()
          this.initialized = true

          if (callback !== null) {
            callback(d['data'])
          }
        }
      })
    }
  }

  loadSessionsByCourse(courseId, callback = null) {
    if (this.courseSessions[courseId] !== undefined) {
      this.computeSessionsStatusByCourse(courseId)
    } else {
      const route = Routing.generate('api_get_sessions_by_course', {course: courseId})
      this.$http.get(route).then(d => {
        if(d['status'] === 200) {
          this.courseSessions[courseId] = d['data']
          this.computeSessionsStatusByCourse(courseId)

          if (callback !== null) {
            callback(d['data'])
          }
        }
      })
    }
  }

  createSession(course, callback = null) {
    const addCallback = (callback !== null) ? callback : this._addSessionCallback
    this.$uibModal.open({
      template: sessionFormTemplate,
      controller: 'SessionCreationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('session_creation', {}, 'cursus') },
        course: () => { return course },
        callback: () => { return addCallback }
      }
    })
  }

  editSession(session, callback = null) {
    const updateCallback = callback !== null ? callback : this._updateSessionCallback
    this.$uibModal.open({
      template: sessionFormTemplate,
      controller: 'SessionEditionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('session_edition', {}, 'cursus') },
        session: () => { return session },
        callback: () => { return updateCallback }
      }
    })
  }

  deleteSession(sessionId, callback = null) {
    const deleteCallback = callback !== null ? callback : this._removeSessionCallback
    this.$uibModal.open({
      template: sessionDeleteTemplate,
      controller: 'SessionDeletionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionId: () => { return sessionId },
        callback: () => { return deleteCallback }
      }
    })
  }

  resetDefaultSession(courseId, sessionId) {
    const route = Routing.generate('api_put_session_default_reset', {course: courseId, session: sessionId})
    this.$http.put(route).then(d => {
      if (d['status'] === 200) {
        this._resetDefaultSessionCallback(courseId, sessionId)
      }
    })
  }

  getSessionStatus(start, end, now = null) {
    let status = ''
    const startDate = new Date(start)
    const endDate = new Date(end)
    const currentDate = (now === null) ? new Date() : now

    if (startDate.getTime() > currentDate.getTime()) {
      status = 'not_started'
    } else if (startDate.getTime() <= currentDate.getTime() && endDate.getTime() > currentDate.getTime()) {
      status = 'ongoing'
    } else if (endDate.getTime() < currentDate.getTime()) {
      status = 'closed'
    }

    return status
  }

  generateCourseInfos() {
    this.sessions.forEach(s => {
      const courseTitle = s['course']['title']
      const courseCode = s['course']['code']
      s['course_title']= courseTitle
      s['course_code']= courseCode
    })
  }

  generateCourseSessions() {
    let coursesDone = {}
    this.sessions.forEach(s => {
      const courseId = s['course']['id']

      if (!coursesDone[courseId]) {
        if (this.courseSessions[courseId]) {
          this.courseSessions[courseId].splice(0, this.courseSessions[courseId].length)
        } else {
          this.courseSessions[courseId] = []
        }
        coursesDone[courseId] = true
      }
      this.courseSessions[courseId].push(s)
    })
  }

  computeSessionsStatus() {
    const now = new Date()

    this.sessions.forEach(s => {
      s['status'] = this.getSessionStatus(s['startDate'], s['endDate'], now)
    })
  }

  computeSessionsStatusByCourse(courseId) {
    const now = new Date()

    if (this.openCourseSessions[courseId]) {
      this.openCourseSessions[courseId].splice(0, this.openCourseSessions[courseId].length)
    } else {
      this.openCourseSessions[courseId] = []
    }

    if (this.closedCourseSessions[courseId]) {
      this.closedCourseSessions[courseId].splice(0, this.closedCourseSessions[courseId].length)
    } else {
      this.closedCourseSessions[courseId] = []
    }

    if (this.courseSessions[courseId]) {
      this.courseSessions[courseId].forEach(s => {
        s['status'] = this.getSessionStatus(s['startDate'], s['endDate'], now)

        if (s['status'] === 'closed') {
          this.closedCourseSessions[courseId].push(s)
        } else {
          this.openCourseSessions[courseId].push(s)
        }
      })
    }
  }

  getSessionById(sessionId) {
    const index = this.sessions.findIndex(s => s['id'] === sessionId)

    if (index > -1) {
      this.session = this.sessions[index]

      return 'initialized'
    } else {
      for (const key in this.session) {
        delete this.session[key]
      }
      const route = Routing.generate('api_get_session_by_id', {session: sessionId})
      return this.$http.get(route).then(d => {
        if (d['status'] === 200) {
          const datas = JSON.parse(d['data'])

          for (const key in datas) {
            this.session[key] = datas[key]
          }

          return 'initialized'
        }
      })
    }
  }

  generateUserDatas(datas) {
    datas['userId'] = datas['user']['id']
    datas['username'] = datas['user']['username']
    datas['firstName'] = datas['user']['firstName']
    datas['lastName'] = datas['user']['lastName']
    datas['fullName'] = `${datas['user']['firstName']} ${datas['user']['lastName']}`

    return datas
  }

  generateGroupDatas(datas) {
    datas['groupId'] = datas['group']['id']
    datas['groupName'] = datas['group']['name']

    return datas
  }

  loadGroupsBySession(sessionId, callback = null) {
    if (this.groups[sessionId] === undefined) {
      const route = Routing.generate('api_get_session_groups_by_session', {session: sessionId})
      this.$http.get(route).then(d => {
        if(d['status'] === 200) {
          this.groups[sessionId] = []
          const datas = JSON.parse(d['data'])
          datas.forEach(sg => {
            this.groups[sessionId].push(this.generateGroupDatas(sg))
          })

          if (callback !== null) {
            callback(datas)
          }
        }
      })
    }
  }

  loadUsersBySession(sessionId, callback = null) {
    if (this.users[sessionId] === undefined) {
      const route = Routing.generate('api_get_session_users_by_session', {session: sessionId})
      this.$http.get(route).then(d => {
        if(d['status'] === 200) {
          this.users[sessionId] = []
          const datas = JSON.parse(d['data'])
          datas.forEach(su => {
            this.users[sessionId].push(this.generateUserDatas(su))
          })
          this.sortSessionUsersbyType(sessionId)

          if (callback !== null) {
            callback(datas)
          }
        }
      })
    }
  }

  loadPendingUsersBySession(sessionId, callback = null) {
    if (!this.pendingInitialized[sessionId]) {
      const route = Routing.generate('api_get_session_pending_users_by_session', {session: sessionId})
      this.$http.get(route).then(d => {
        if(d['status'] === 200) {
          if (this.pendingLearners[sessionId] === undefined) {
            this.pendingLearners[sessionId] = []
          } else {
            this.pendingLearners[sessionId].splice(0, this.pendingLearners[sessionId].length)
          }
          const datas = JSON.parse(d['data'])
          datas.forEach(pu => {
            this.pendingLearners[sessionId].push(this.generateUserDatas(pu))
          })
          this.pendingInitialized[sessionId] = true

          if (callback !== null) {
            callback(datas)
          }
        }
      })
    }
  }

  sortSessionUsersbyType(sessionId) {
    if (this.learners[sessionId]) {
      this.learners[sessionId].splice(0, this.learners[sessionId].length)
    } else {
      this.learners[sessionId] = []
    }

    if (this.tutors[sessionId]) {
      this.tutors[sessionId].splice(0, this.tutors[sessionId].length)
    } else {
      this.tutors[sessionId] = []
    }
    this.users[sessionId].forEach(u => {
      if (u['userType'] === 0) {
        this.learners[sessionId].push(u)
      } else if (u['userType'] === 1) {
        this.tutors[sessionId].push(u)
      }
    })
  }

  deleteTutor(sessionUserId, callback = null) {
    const url = Routing.generate('api_delete_session_user', {sessionUser: sessionUserId})
    const deleteCallback = (callback !== null) ? callback : this._removeTutorCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('unregister_tutor_from_session', {}, 'cursus'),
      Translator.trans('unregister_tutor_from_session_confirm_message', {}, 'cursus')
    )
  }

  deleteLearner(sessionUserId, callback = null) {
    const url = Routing.generate('api_delete_session_user', {sessionUser: sessionUserId})
    const deleteCallback = (callback !== null) ? callback : this._removeLearnerCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('unregister_learner_from_session', {}, 'cursus'),
      Translator.trans('unregister_learner_from_session_confirm_message', {}, 'cursus')
    )
  }

  deleteGroup(sessionGroupId, callback = null) {
    const url = Routing.generate('api_delete_session_group', {sessionGroup: sessionGroupId})
    const deleteCallback = (callback !== null) ? callback : this._removeGroupCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('unregister_group_from_session', {}, 'cursus'),
      Translator.trans('unregister_group_from_session_message', {}, 'cursus')
    )
  }

  sendConfirmationMail(sessionId, userId) {
    const url = Routing.generate('claro_cursus_course_session_user_confirmation_mail_send', {session: sessionId, user: userId})

    this.ClarolineAPIService.confirm(
      {url, method: 'POST'},
      () => {},
      Translator.trans('send_confirmation_mail', {}, 'cursus'),
      Translator.trans('send_confirmation_mail_to_user_message', {}, 'cursus')
    )
  }

  acceptQueue(queueId, callback = null) {
    const url = Routing.generate('api_accept_session_registration_queue', {queue: queueId})
    const acceptCallback = (callback !== null) ? callback : this._acceptQueueCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'POST'},
      acceptCallback,
      Translator.trans('accept_registration', {}, 'cursus'),
      Translator.trans('accept_registration_confirm_message', {}, 'cursus')
    )
  }

  declineQueue(queueId, callback = null) {
    const url = Routing.generate('api_delete_session_registration_queue', {queue: queueId})
    const deleteCallback = (callback !== null) ? callback : this._removePendingLearnerCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('decline_registration', {}, 'cursus'),
      Translator.trans('decline_registration_confirm_message', {}, 'cursus')
    )
  }

  registerLearners(sessionId, callback = null) {
    const addCallback = (callback !== null) ? callback : this._addLearnersCallback
    this.$uibModal.open({
      template: learnersRegistrationTemplate,
      controller: 'UsersRegistrationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionId: () => { return sessionId },
        userType: () => { return 0 },
        callback: () => { return addCallback }
      }
    })
  }

  registerLearnersGroups(sessionId, callback = null) {
    const addCallback = (callback !== null) ? callback : this._addLearnersGroupsCallback
    this.$uibModal.open({
      template: learnersGroupsRegistrationTemplate,
      controller: 'GroupsRegistrationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionId: () => { return sessionId },
        groupType: () => { return 0 },
        callback: () => { return addCallback }
      }
    })
  }

  registerTutors(sessionId, callback = null) {
    const addCallback = (callback !== null) ? callback : this._addTutorsCallback
    this.$uibModal.open({
      template: tutorsRegistrationTemplate,
      controller: 'UsersRegistrationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionId: () => { return sessionId },
        userType: () => { return 1 },
        callback: () => { return addCallback }
      }
    })
  }

  sendMessageToSession(session) {
    this.$uibModal.open({
      template: sessionMessageTemplate,
      controller: 'SessionMessageModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        session: () => { return session }
      }
    })
  }

  exportUsersForm(sessionId) {
    this.$uibModal.open({
      template: sessionUsersExportTemplate,
      controller: 'SessionUsersExportModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionId: () => { return sessionId }
      }
    })
  }

  exportUsers(sessionId, type) {
    const url = Routing.generate('api_get_session_users_csv_export', {session: sessionId, type: type})
    window.location.href = url
  }
}