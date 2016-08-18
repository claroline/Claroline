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
import sessionEventFormTemplate from '../Partial/session_event_form_modal.html'
import sessionEventRepeatModalTemplate from '../Partial/session_event_repeat_form_modal.html'
import sessionEventCommentsManagementTemplate from '../Partial/session_event_comments_management_modal.html'

export default class SessionEventService {
  constructor ($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.sessionEvents = {}
    this.openSessionEvents = {}
    this.closedSessionEvents = {}
    this._addSessionEventCallback = this._addSessionEventCallback.bind(this)
    this._addMultipleSessionEventsCallback = this._addMultipleSessionEventsCallback.bind(this)
    this._updateSessionEventCallback = this._updateSessionEventCallback.bind(this)
    this._removeSessionEventCallback = this._removeSessionEventCallback.bind(this)
  }

  _addSessionEventCallback(data) {
    const eventJson = JSON.parse(data)

    if (eventJson['session']['id']) {
      const sessionId = eventJson['session']['id']
      this.sessionEvents[sessionId].push(eventJson)
      this.computeSessionEventsStatusBySession(sessionId)
    }
  }

  _addMultipleSessionEventsCallback(data) {
    const eventsJson = JSON.parse(data)
    let sessionIds = {}
    eventsJson.forEach(e => {
      if (e['session']['id']) {
        const sessionId = e['session']['id']
        sessionIds[sessionId] = sessionId
        this.sessionEvents[sessionId].push(e)
      }
    })

    for (let sessionId in sessionIds) {
      this.computeSessionEventsStatusBySession(sessionId)
    }
  }

  _updateSessionEventCallback(data) {
    const eventJson = JSON.parse(data)
    const sessionId = eventJson['session']['id']
    const eventIndex = this.sessionEvents[sessionId].findIndex(e => e['id'] === eventJson['id'])

    if (eventIndex > -1) {
      this.sessionEvents[sessionId][eventIndex] = eventJson
    }
    this.computeSessionEventsStatusBySession(sessionId)
  }

  _removeSessionEventCallback(data) {
    const eventJson = JSON.parse(data)
    const sessionId = eventJson['session']['id']
    const eventIndex = this.sessionEvents[sessionId].findIndex(e => e['id'] === eventJson['id'])

    if (eventIndex > -1) {
      this.sessionEvents[sessionId].splice(eventIndex, 1)
    }
    this.computeSessionEventsStatusBySession(sessionId)
  }

  getSessionEvents () {
    return this.sessionEvents
  }

  getOpenSessionEvents () {
    return this.openSessionEvents
  }

  getOpenSessionEventsBySession (sessionId) {
    if (!this.openSessionEvents[sessionId]) {
      this.openSessionEvents[sessionId] = []
    }

    return this.openSessionEvents[sessionId]
  }

  getClosedSessionEvents () {
    return this.closedSessionEvents
  }

  getClosedSessionEventsBySession (sessionId) {
    if (!this.closedSessionEvents[sessionId]) {
      this.closedSessionEvents[sessionId] = []
    }

    return this.closedSessionEvents[sessionId]
  }

  loadEventsBySession (sessionId, callback = null) {
    if (this.sessionEvents[sessionId] !== undefined) {
      this.computeSessionEventsStatusBySession(sessionId)
    } else {
      const route = Routing.generate('api_get_session_events_by_session', {session: sessionId})
      this.$http.get(route).then(d => {
        if(d['status'] === 200) {
          this.sessionEvents[sessionId] = d['data']
          this.computeSessionEventsStatusBySession(sessionId)

          if (callback !== null) {
            callback(d['data'])
          }
        }
      })
    }
  }

  createSessionEvent (session, callback = null) {
    const addCallback = (callback !== null) ? callback : this._addSessionEventCallback
    this.$uibModal.open({
      template: sessionEventFormTemplate,
      controller: 'SessionEventCreationModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('session_event_creation', {}, 'cursus') },
        session: () => { return session },
        callback: () => { return addCallback }
      }
    })
  }

  editSessionEvent (sessionEvent, callback = null) {
    const updateCallback = callback !== null ? callback : this._updateSessionEventCallback
    this.$uibModal.open({
      template: sessionEventFormTemplate,
      controller: 'SessionEventEditionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        title: () => { return Translator.trans('session_event_edition', {}, 'cursus') },
        sessionEvent: () => { return sessionEvent },
        callback: () => { return updateCallback }
      }
    })
  }

  deleteSessionEvent (sessionEventId, callback = null) {
    const url = Routing.generate('api_delete_session_event', {sessionEvent: sessionEventId})
    const deleteCallback = (callback !== null) ? callback : this._removeSessionEventCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_session_event', {}, 'cursus'),
      Translator.trans('delete_session_event_confirm_message', {}, 'cursus')
    )
  }

  getSessionEventStatus (start, end, now = null) {
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

  computeSessionEventsStatusBySession (sessionId) {
    const now = new Date()

    if (this.openSessionEvents[sessionId]) {
      this.openSessionEvents[sessionId].splice(0, this.openSessionEvents[sessionId].length)
    } else {
      this.openSessionEvents[sessionId] = []
    }

    if (this.closedSessionEvents[sessionId]) {
      this.closedSessionEvents[sessionId].splice(0, this.closedSessionEvents[sessionId].length)
    } else {
      this.closedSessionEvents[sessionId] = []
    }

    this.sessionEvents[sessionId].forEach(e => {
      if (e['location']) {
        e['address'] = `${e['location']['street']}, ${e['location']['street_number']}<br>${e['location']['pc']} ${e['location']['town']}<br>${e['location']['country']}`
      }
      e['status'] = this.getSessionEventStatus(e['startDate'], e['endDate'], now)

      if (e['status'] === 'closed') {
        this.closedSessionEvents[sessionId].push(e)
      } else {
        this.openSessionEvents[sessionId].push(e)
      }
    })
  }

  repeatEvent (sessionEvent, callback = null) {
    const addCallback = callback !== null ? callback : this._addMultipleSessionEventsCallback
    this.$uibModal.open({
      template: sessionEventRepeatModalTemplate,
      controller: 'SessionEventRepeatModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionEvent: () => { return sessionEvent },
        callback: () => { return addCallback }
      }
    })
  }

  manageComments (sessionEvent) {
    this.$uibModal.open({
      template: sessionEventCommentsManagementTemplate,
      controller: 'SessionEventCommentsManagementModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        sessionEvent: () => { return sessionEvent }
      }
    })
  }

  createComment (sessionEventId, content) {
    const url = Routing.generate('api_post_session_event_comment', {sessionEvent: sessionEventId})

    return this.$http.post(url, {comment: content}).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  editComment (commentId, content) {
    const url = Routing.generate('api_put_session_event_comment_edit', {sessionEventComment: commentId})

    return this.$http.put(url, {comment: content}).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  deleteComment (commentId) {
    const url = Routing.generate('api_delete_session_event_comment', {sessionEventComment: commentId})

    return this.$http.delete(url).then(d => {
      if (d['status'] === 200) {
        return d['data']
      }
    })
  }
}