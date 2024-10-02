import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {selectors} from '#/main/core/tools/locations//store/selectors'

export const actions = {}

actions.open = (id = null) => (dispatch) => {
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
