import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {Location as LocationTypes} from '#/main/core/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.groups'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.organizations'))
  dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.users'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_location_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  }

  return dispatch(formActions.resetForm(formName, LocationTypes.defaultProps, true))
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_location_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.users'))
    }
  }
})

actions.addGroups = (id, groups) => ({
  [API_REQUEST]: {
    url: url(['apiv2_location_add_groups', {id: id}], {ids: groups}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.groups'))
    }
  }
})

actions.addOrganizations = (id, organizations) => ({
  [API_REQUEST]: {
    url: url(['apiv2_location_add_organizations', {id: id}], {ids: organizations}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.list'))
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.current.organizations'))
    }
  }
})

actions.geolocate = (location) => ({
  [API_REQUEST]: {
    url: ['apiv2_location_geolocate', {id: location.id}],
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(baseSelectors.STORE_NAME+'.locations.list'))
    }
  }
})
