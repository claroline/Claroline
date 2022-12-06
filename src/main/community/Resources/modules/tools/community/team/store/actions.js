import merge from 'lodash/merge'

import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/community/tools/community/team/store/selectors'
import {Team as TeamTypes} from '#/main/community/team/prop-types'

export const MY_TEAMS_ADD = 'MY_TEAMS_ADD'
export const MY_TEAMS_REMOVE = 'MY_TEAMS_REMOVE'

export const actions = {}

actions.addToMyTeams = makeActionCreator(MY_TEAMS_ADD, 'team')
actions.removeFromMyTeams = makeActionCreator(MY_TEAMS_REMOVE, 'team')

actions.new = (defaultProps) => formActions.resetForm(selectors.FORM_NAME, merge({}, TeamTypes.defaultProps, defaultProps), true)

actions.open = (id, reload = false) => (dispatch) => {
  if (!reload) {
    // remove previous team if any to avoid displaying it while loading
    dispatch(formActions.resetForm(selectors.FORM_NAME, {}, false))
  }

  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.users'))
  dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_team_get', {id}],
      silent: true,
      success: (response) => dispatch(formActions.resetForm(selectors.FORM_NAME, response, false))
    }
  })
}

actions.addUsers = (id, users) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_team_register', {id: id, role: 'user'}], {ids: users}),
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
    url: url(['apiv2_team_register', {id: id, role: 'manager'}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.LIST_NAME))
      dispatch(listActions.invalidateData(selectors.FORM_NAME+'.managers'))
    }
  }
})

actions.selfRegister = (team) => ({
  [API_REQUEST]: {
    url: ['apiv2_team_self_register', {id: team.id}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.addToMyTeams(team))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))

      // reload the current team to get the correct users count
      dispatch(actions.open(team.id, true))
    }
  }
})

actions.selfUnregister = (team) => ({
  [API_REQUEST]: {
    url: ['apiv2_team_self_unregister', {id: team.id}],
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeFromMyTeams(team))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.teams.list'))

      // reload the current team to get the correct users count
      dispatch(actions.open(team.id, true))
    }
  }
})
