import isEmpty from 'lodash/isEmpty'

import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {constants as actionConstants} from '#/main/app/action/constants'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'

export const LOAD_COURSE = 'LOAD_COURSE'
export const LOAD_COURSE_SESSION = 'LOAD_COURSE_SESSION'

export const actions = {}

actions.loadCourse = makeActionCreator(LOAD_COURSE, 'course', 'defaultSession', 'availableSessions', 'registrations')
actions.loadSession = makeActionCreator(LOAD_COURSE_SESSION, 'session')

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
      url: url(['apiv2_cursus_course_find'], {filters: {slug: courseSlug}}),
      silent: true,
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
          success: (data) => dispatch(actions.loadSession(data))
        }
      })
    }
  } else {
    dispatch(actions.loadSession(null))
  }
}

actions.addCourseUsers = (courseId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_course_add_users', {id: courseId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.coursePending'))
    }
  }
})

actions.moveCourseUsers = (courseId, targetId, courseUsers) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_course_move_users', {id: courseId}],
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

// Sessions registration management

actions.addUsers = (sessionId, users, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_users', {id: sessionId, type: type}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.inviteUsers = (sessionId, users) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_cursus_session_invite_users', {id: sessionId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.moveUsers = (sessionId, targetId, sessionUsers, type) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_move_users', {id: sessionId, type: type}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        target: targetId,
        sessionUsers: sessionUsers.map(sessionUser => sessionUser.id)
      })
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.addGroups = (sessionId, groups, type) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_groups', {id: sessionId, type: type}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.inviteGroups = (sessionId, groups) => ({
  [API_REQUEST]: {
    type: actionConstants.ACTION_SEND,
    url: url(['apiv2_cursus_session_invite_groups', {id: sessionId}], {ids: groups.map(group => group.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.moveGroups = (sessionId, targetId, sessionGroups, type) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_move_groups', {id: sessionId, type: type}],
    request: {
      method: 'PUT',
      body: JSON.stringify({
        target: targetId,
        sessionGroups: sessionGroups.map(sessionGroup => sessionGroup.id)
      })
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.addPending = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_pending', {id: sessionId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.confirmPending = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_confirm_pending', {id: sessionId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.validatePending = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_validate_pending', {id: sessionId}], {ids: users.map(user => user.id)}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      // TODO : do something better (I need it to recompute session available space)
      dispatch(actions.openSession(sessionId, true))
    }
  }
})

actions.register = (course, sessionId = null) => ({
  [API_REQUEST]: {
    url: sessionId ?
      ['apiv2_cursus_session_self_register', {id: sessionId}] :
      ['apiv2_cursus_course_self_register', {id: course.id}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.open(course.slug, true))
  }
})
