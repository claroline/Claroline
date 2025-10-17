import {API_REQUEST} from '#/main/app/api'

import {makeActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {selectors} from '#/main/core/tools/locations/store/selectors'

export const LOCATION_LOAD = 'LOCATION_LOAD'

export const actions = {}

actions.loadLocation = makeActionCreator(LOCATION_LOAD, 'location')

actions.openLocation = (id) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_location_get', {id: id}],
    silent: true,
    success: (response) => dispatch(actions.loadLocation(response))
  }
})

actions.invalidateLocations = () => listActions.invalidateData(selectors.STORE_NAME+'.list')
