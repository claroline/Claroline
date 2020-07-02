import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {constants} from '#/plugin/cursus/administration/cursus/constants'
import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_session_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.reset = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
  dispatch(listActions.invalidateData(formName+'.events', {}, true))
  dispatch(listActions.invalidateData(formName+'.learners', {}, true))
  dispatch(listActions.invalidateData(formName+'.teachers', {}, true))
  dispatch(listActions.invalidateData(formName+'.groups', {}, true))
}

actions.addUsers = (sessionId, users, type = constants.LEARNER_TYPE) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_users', {id: sessionId, type: type}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      switch (type) {
        case constants.LEARNER_TYPE:
          dispatch(listActions.invalidateData(selectors.STORE_NAME + '.sessions.current.learners'))
          break
        case constants.TEACHER_TYPE:
          dispatch(listActions.invalidateData(selectors.STORE_NAME + '.sessions.current.teachers'))
          break
      }
    }
  }
})

actions.addGroups = (sessionId, groups, type = constants.LEARNER_TYPE) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_add_groups', {id: sessionId, type: type}], {ids: groups.map(g => g.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.sessions.current.groups'))
    }
  }
})

actions.inviteAll = (sessionId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_invite_all', {id: sessionId}],
    request: {
      method: 'PUT'
    }
  }
})

actions.inviteUsers = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_invite_users', {id: sessionId}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.inviteGroups = (sessionId, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_invite_groups', {id: sessionId}], {ids: groups.map(g => g.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.generateAllCertificates = (sessionId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_certificate_generate_all', {id: sessionId}],
    request: {
      method: 'PUT'
    }
  }
})

actions.generateUsersCertificates = (sessionId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_certificate_generate_users', {id: sessionId}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.generateGroupsCertificates = (sessionId, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_certificate_generate_groups', {id: sessionId}], {ids: groups.map(g => g.id)}),
    request: {
      method: 'PUT'
    }
  }
})
