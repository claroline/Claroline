import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/administration/cursus/store/selectors'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_session_event_get', {id}],
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
  dispatch(listActions.invalidateData(formName+'.users', {}, true))
}

actions.addUsers = (sessionEventId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_event_add_users', {id: sessionEventId}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME + '.events.current.users'))
  }
})

actions.inviteAll = (sessionEventId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_event_invite_all', {id: sessionEventId}],
    request: {
      method: 'PUT'
    }
  }
})

actions.inviteUsers = (sessionEventId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_event_invite_users', {id: sessionEventId}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  }
})

actions.generateAllCertificates = (sessionEventId) => ({
  [API_REQUEST]: {
    url: ['apiv2_cursus_session_event_certificate_generate_all', {id: sessionEventId}],
    request: {
      method: 'PUT'
    }
  }
})

actions.generateUsersCertificates = (sessionEventId, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_cursus_session_event_certificate_generate_users', {id: sessionEventId}], {ids: users.map(u => u.id)}),
    request: {
      method: 'PUT'
    }
  }
})
