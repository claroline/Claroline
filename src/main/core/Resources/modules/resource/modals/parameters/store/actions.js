import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/resource/modals/parameters/store/selectors'

export const actions = {}

actions.get = (resourceID) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['claro_resource_load', {id: resourceID}],
    silent: true,
    success: (data) => dispatch( formActions.resetForm(selectors.STORE_NAME, data.resourceNode ))
  }
})
