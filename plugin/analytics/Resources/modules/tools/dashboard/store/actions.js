import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/analytics/tools/dashboard/store/selectors'

const LOAD_DASHBOARD = 'LOAD_DASHBOARD'
const LOAD_ANALYTICS = 'LOAD_ANALYTICS'
const LOAD_REQUIREMENTS = 'LOAD_REQUIREMENTS'

const actions = {}

actions.loadDashboard = makeActionCreator(LOAD_DASHBOARD, 'data')
actions.loadAnalytics = makeActionCreator(LOAD_ANALYTICS, 'data')
actions.loadRequirements = makeActionCreator(LOAD_REQUIREMENTS, 'data')

actions.getDashboardData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadDashboard({}))
  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: url([route, params]) + queryString,
        success: (response, dispatch) => {
          dispatch(actions.loadDashboard(response))
        },
        error: (err, status, dispatch) => {
          dispatch(actions.loadDashboard({}))
        }
      }
    })
  }
}

actions.getAnalyticsData = (route, params = {}, queryString = '') => (dispatch) => {
  dispatch(actions.loadAnalytics({}))

  if (route) {
    dispatch({
      [API_REQUEST]: {
        url: url([route, params]) + queryString,
        success: (response, dispatch) => dispatch(actions.loadAnalytics(response)),
        error: (err, status, dispatch) => dispatch(actions.loadAnalytics({}))
      }
    })
  }
}

actions.openRequirements = (id) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: ['apiv2_workspace_requirements_fetch', {requirements: id}],
      request: {
        method: 'GET'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}

actions.createRequirementsForRoles = (workspace, roles) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_create', {workspace: workspace.uuid, type: 'role'}], {ids: roles.map(r => r.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME + '.requirements.roles'))
    }
  })
}

actions.createRequirementsForUsers = (workspace, users) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_create', {workspace: workspace.uuid, type: 'user'}], {ids: users.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(listActions.invalidateData(selectors.STORE_NAME + '.requirements.users'))
    }
  })
}

actions.addRequirementsResources = (requirements, resources) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_resources_add', {requirements: requirements.id}], {ids: resources.map(r => r.id)}),
      request: {
        method: 'PUT'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}

actions.removeRequirementsResources = (requirements, resources) => (dispatch) => {
  dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_workspace_requirements_resources_remove', {requirements: requirements.id}], {ids: resources.map(r => r.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response, dispatch) => dispatch(actions.loadRequirements(response))
    }
  })
}

export {
  actions,
  LOAD_DASHBOARD,
  LOAD_ANALYTICS,
  LOAD_REQUIREMENTS
}
