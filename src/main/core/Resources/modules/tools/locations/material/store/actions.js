import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {Material as MaterialTypes} from '#/main/core/tools/locations/prop-types'
import {selectors} from '#/main/core/tools/locations/material/store/selectors'

export const actions = {}

actions.open = (id = null) => (dispatch) => {
  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_booking_material_get', {id: id}],
        silent: true,
        before: () => dispatch(formActions.reset(selectors.FORM_NAME, null, false)),
        success: (response) => dispatch(formActions.reset(selectors.FORM_NAME, response, false))
      }
    })
  }

  return dispatch(formActions.reset(selectors.FORM_NAME, MaterialTypes.defaultProps, true))
}
