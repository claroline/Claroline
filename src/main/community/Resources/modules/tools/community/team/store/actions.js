import merge from 'lodash/merge'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/tools/community/team/store/selectors'
import {Team as TeamTypes} from '#/main/community/team/prop-types'

export const actions = {}

actions.new = (defaultProps) => formActions.resetForm(selectors.FORM_NAME, merge({}, TeamTypes.defaultProps, defaultProps), true)

actions.open = (id, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous group if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_team_get', {id}],
      success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addUsers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_team_register', {team: id, role: 'user'}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
    }
  }
})

actions.addManagers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_team_register', {team: id, role: 'manager'}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))
    }
  }
})

actions.selfRegister = (teamId) => ({
  [API_REQUEST]: {
    url: ['apiv2_team_self_register', {team: teamId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.addToMyTeams(teamId))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.current.users'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))
    }
  }
})

actions.selfUnregister = (teamId) => ({
  [API_REQUEST]: {
    url: ['apiv2_team_self_unregister', {team: teamId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeFromMyTeams(teamId))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.current.users'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))
    }
  }
})

actions.fillTeams = (teams) => ({
  [API_REQUEST]: {
    url: url(['apiv2_team_fill'], {ids: teams.map(t => t.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))
    }
  }
})

actions.emptyTeams = (teams) => ({
  [API_REQUEST]: {
    url: url(['apiv2_team_empty'], {ids: teams.map(t => t.id)}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))
    }
  }
})