import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

const MY_TEAMS_ADD = 'MY_TEAMS_ADD'
const MY_TEAMS_REMOVE = 'MY_TEAMS_REMOVE'

const actions = {}

actions.addToMyTeams = makeActionCreator(MY_TEAMS_ADD, 'teamId')
actions.removeFromMyTeams = makeActionCreator(MY_TEAMS_REMOVE, 'teamId')

actions.openForm = (formName, id = null, defaultProps) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_team_get', {id}],
        success: (data, dispatch) => dispatch(formActions.resetForm(formName, data, false))
      }
    }
  } else {
    return formActions.resetForm(formName, defaultProps, true)
  }
}

actions.registerUsers = (teamId, users, role = 'user') => ({
  [API_REQUEST]: {
    url: url(['apiv2_team_register', {team: teamId, role: role}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      switch (role) {
        case 'user':
          dispatch(listActions.invalidateData('teams.current.users'))
          break
        case 'manager':
          dispatch(listActions.invalidateData('teams.current.managers'))
          break
      }
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
      dispatch(listActions.invalidateData('teams.current.users'))
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
      dispatch(listActions.invalidateData('teams.current.users'))
    }
  }
})

actions.fillTeams = (teams) => ({
  [API_REQUEST]: {
    url: url(['apiv2_team_fill'], {ids: teams.map(t => t.id)}),
    request: {
      method: 'PATCH'
    }
  }
})

actions.emptyTeams = (teams) => ({
  [API_REQUEST]: {
    url: url(['apiv2_team_empty'], {ids: teams.map(t => t.id)}),
    request: {
      method: 'PATCH'
    }
  }
})

export {
  actions,
  MY_TEAMS_ADD,
  MY_TEAMS_REMOVE
}