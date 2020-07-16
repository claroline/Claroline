import isEmpty from 'lodash/isEmpty'

import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/cursus/tools/cursus/catalog/store/selectors'

export const LOAD_COURSE = 'LOAD_COURSE'
export const LOAD_COURSE_SESSION = 'LOAD_COURSE_SESSION'
export const LOAD_SESSION_USER = 'LOAD_SESSION_USER'
export const LOAD_SESSION_QUEUE = 'LOAD_SESSION_QUEUE'
export const LOAD_SESSION_FULL = 'LOAD_SESSION_FULL'
export const LOAD_EVENTS_REGISTRATION = 'LOAD_EVENTS_REGISTRATION'

export const actions = {}

actions.loadCourse = makeActionCreator(LOAD_COURSE, 'course', 'defaultSession', 'availableSessions')
actions.loadSession = makeActionCreator(LOAD_COURSE_SESSION, 'session')

actions.open = (courseSlug, force = false) => (dispatch, getState) => {
  const currentCourse = selectors.course(getState())
  if (force || isEmpty(currentCourse) || currentCourse.slug !== courseSlug) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_course_open', {slug: courseSlug}],
        silent: true,
        before: () => dispatch(actions.loadCourse(null, null, [])),
        success: (data) => dispatch(actions.loadCourse(data.course, data.defaultSession, data.availableSessions))
      }
    })
  }
}

actions.openForm = (courseSlug = null, defaultProps = {}) => (dispatch) => {
  if (!courseSlug) {
    return dispatch(formActions.resetForm(selectors.FORM_NAME, defaultProps, true))
  }

  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_cursus_course_find'], {filters: {slug: courseSlug}}),
      silent: true,
      success: (data) => dispatch(formActions.resetForm(selectors.FORM_NAME, data))
    }
  })
}

actions.openSession = (sessionId) => (dispatch, getState) => {
  const currentSession = selectors.activeSession(getState())
  if (isEmpty(currentSession) || currentSession.id !== sessionId) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_session_get', {id: sessionId}],
        silent: true,
        success: (data) => dispatch(actions.loadSession(data))
      }
    })
  }
}

actions.register = (sessionId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_self_register', {id: sessionId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      if (data.registrationDate) {
        dispatch(actions.loadSessionUser(data))
      } else if (data.applicationDate) {
        dispatch(actions.loadSessionQueue(data))
      }
    }
  }
})

/*actions.fetchSession = (sessionId) => ({
  [API_REQUEST]: {
    url: ['claro_cursus_catalog_session', {session: sessionId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.loadSession(data['session']))
      dispatch(actions.loadSessionUser(data['sessionUser']))
      dispatch(actions.loadSessionQueue(data['sessionQueue']))
      dispatch(actions.loadIsFull(data['isFull']))
      dispatch(actions.loadEventsRegistration(data['eventsRegistration']))
    }
  }
})*/

actions.registerToEvent = (eventId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_event_self_register', {id: eventId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.loadEventsRegistration(data))
  }
})

//actions.loadSession = makeActionCreator(LOAD_SESSION, 'session')
actions.loadSessionUser = makeActionCreator(LOAD_SESSION_USER, 'sessionUser')
actions.loadSessionQueue = makeActionCreator(LOAD_SESSION_QUEUE, 'sessionQueue')
actions.loadIsFull = makeActionCreator(LOAD_SESSION_FULL, 'isFull')
actions.loadEventsRegistration = makeActionCreator(LOAD_EVENTS_REGISTRATION, 'eventsRegistration')
