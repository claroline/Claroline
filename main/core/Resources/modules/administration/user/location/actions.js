import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

import {Location as LocationTypes} from '#/main/core/user/prop-types'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_location_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, true))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, LocationTypes.defaultProps, true))
  }
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_location_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('locations.list'))
      dispatch(listActions.invalidateData('locations.current.users'))
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
      dispatch(listActions.invalidateData('locations.list'))
      dispatch(listActions.invalidateData('locations.current.groups'))
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
      dispatch(listActions.invalidateData('locations.list'))
      dispatch(listActions.invalidateData('locations.current.organizations'))
    }
  }
})

actions.geolocate = (location) => ({
  [API_REQUEST]: {
    url: ['apiv2_location_geolocate', {id: location.id}],
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('locations.list'))
    }
  }
})
