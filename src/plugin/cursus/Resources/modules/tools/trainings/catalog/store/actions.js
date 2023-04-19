import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {constants as actionConstants} from '#/main/app/action/constants'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'

export const LOAD_COURSE = 'LOAD_COURSE'
export const LOAD_COURSE_SESSION = 'LOAD_COURSE_SESSION'
export const LOAD_COURSE_STATS = 'LOAD_COURSE_STATS'

export const SWITCH_PARTICIPANTS_VIEW = 'SWITCH_PARTICIPANTS_VIEW'

export const actions = {}

actions.loadCourse = makeActionCreator(LOAD_COURSE, 'course', 'defaultSession', 'availableSessions', 'registrations')
actions.loadSession = makeActionCreator(LOAD_COURSE_SESSION, 'session')
actions.loadStats = makeActionCreator(LOAD_COURSE_STATS, 'stats')
actions.switchParticipantsView = makeActionCreator(SWITCH_PARTICIPANTS_VIEW, 'viewMode')

actions.open = (courseSlug, force = false) => (dispatch, getState) => {
  const currentCourse = selectors.course(getState())
  if (force || isEmpty(currentCourse) || currentCourse.slug !== courseSlug) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_course_open', {slug: courseSlug}],
        silent: true,
        before: () => dispatch(actions.loadCourse(null, null, [], {})),
        success: (data) => dispatch(actions.loadCourse(data.course, data.defaultSession, data.availableSessions, data.registrations))
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
      url: ['apiv2_cursus_course_get', {field: 'slug', id: courseSlug}],
      silent: true,
      before: () => dispatch(formActions.resetForm(selectors.FORM_NAME, null, false)),
      success: (data) => dispatch(formActions.resetForm(selectors.FORM_NAME, data))
    }
  })
}

actions.openSession = (sessionId = null, force = false) => (dispatch, getState) => {
  if (sessionId) {
    const currentSession = selectors.activeSession(getState())
    if (force || isEmpty(currentSession) || currentSession.id !== sessionId) {
      return dispatch({
        [API_REQUEST]: {
          url: ['apiv2_cursus_session_get', {id: sessionId}],
          silent: true,
          success: (data) => {
            dispatch(actions.loadSession(data))

            if (!force) {
              dispatch(actions.switchParticipantsView('session'))
            }
          }
        }
      })
    }
  } else {
    dispatch(actions.switchParticipantsView('course'))
    dispatch(actions.loadSession(null))
  }
}

actions.addCourseUsers = (courseId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_course_add_pending', {id: courseId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.coursePending'))
    }
  }
})

actions.updateCourseUser = (courseUser) => ({
  [API_REQUEST]: {
    url: ['apiv2_training_course_user_update', {id: courseUser.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(courseUser)
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME+'.coursePending'))
  }
})

actions.moveCourseUsers = (courseId, targetId, courseUsers) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_course_move_pending', {id: courseId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        target: targetId,
        courseUsers: courseUsers.map(courseUser => courseUser.id)
      })
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.coursePending'))
    }
  }
})

actions.movePending = (courseId, sessionUsers) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_course_move_to_pending', {id: courseId}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        sessionUsers: sessionUsers.map(sessionUser => sessionUser.id)
      })
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.coursePending'))
      dispatch(actions.openSession(sessionUsers[0].session.id, true))
    }
  }
})

// Sessions registration management

actions.addUsers = (sessionId, users, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_users', {id: sessionId, type: type}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.openSession(sessionId, true))
  }
})

actions.inviteUsers = (sessionUsers) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_training_session_user_invite'], {ids: sessionUsers.map(user => user.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.moveUsers = (targetId, sessionUsers, type) => (dispatch, getState) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_training_session_user_move', {targetId: targetId, type: type}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        sessionUsers: sessionUsers.map(sessionUser => sessionUser.id)
      })
    },
    success: () => {
      const currentSession = selectors.activeSession(getState())
      dispatch(actions.openSession(currentSession ? currentSession.id : null, true))
    }
  }
})

actions.updateUser = (sessionUser) => ({
  [API_REQUEST]: {
    url: ['apiv2_training_session_user_update', {id: sessionUser.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(sessionUser)
    },
    success: (data, dispatch) => dispatch(actions.openSession(get(sessionUser, 'session.id'), true))
  }
})

actions.addGroups = (sessionId, groups, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_groups', {id: sessionId, type: type}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.openSession(sessionId, true))
  }
})

actions.inviteGroups = (groups) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_training_session_group_invite'], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.moveGroups = (targetId, sessionGroups, type) => (dispatch, getState) => ({
  [API_REQUEST]: {
    url: ['apiv2_training_session_group_move', {targetId: targetId, type: type}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        sessionGroups: sessionGroups.map(sessionGroup => sessionGroup.id)
      })
    },
    success: () => {
      const currentSession = selectors.activeSession(getState())
      dispatch(actions.openSession(currentSession ? currentSession.id : null, true))
    }
  }
})

actions.addPending = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_pending', {id: sessionId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(actions.openSession(sessionId, true))
  }
})

actions.confirmPending = (users) => (dispatch, getState) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_training_session_user_confirm'], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    },
    success: () => {
      const currentSession = selectors.activeSession(getState())
      dispatch(actions.openSession(currentSession ? currentSession.id : null, true))
    }
  }
})

actions.validatePending = (users) => (dispatch, getState) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_validate_pending'], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    },
    success: () => {
      const currentSession = selectors.activeSession(getState())
      dispatch(actions.openSession(currentSession ? currentSession.id : null, true))
    }
  }
})

actions.register = (course, sessionId = null, registrationData = null) => ({
  [API_REQUEST]: {
    url: sessionId ?
      ['apiv2_cursus_session_self_register', {id: sessionId}] :
      ['apiv2_cursus_course_self_register', {id: course.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(registrationData)
    },
    success: (response, dispatch) => dispatch(actions.open(course.slug, true))
  }
})

actions.fetchStats = (courseId, sessionId = null) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: sessionId ?
      ['apiv2_cursus_session_stats', {id: sessionId}] :
      ['apiv2_cursus_course_stats', {id: courseId}],
    success: (response) => dispatch(actions.loadStats(response))
  }
})
