import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {selectors} from '#/main/core/tools/locations/location/store'

export const actions = {}

actions.open = (id = null) => (dispatch) => {
  // invalidate embedded lists
  dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.groups'))
  dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.organizations'))
  dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.users'))

  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_location_get', {id}],
        silent: true,
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.current', response, false))
        }
      }
    })
  }

  return dispatch(formActions.resetForm(selectors.STORE_NAME+'.current', LocationTypes.defaultProps, true))
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_location_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.users'))
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
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.groups'))
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
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.list'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.current.organizations'))
    }
  }
})

actions.geolocate = (location) => ({
  [API_REQUEST]: {
    url: ['apiv2_location_geolocate', {id: location.id}],
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.list'))
    }
  }
})
