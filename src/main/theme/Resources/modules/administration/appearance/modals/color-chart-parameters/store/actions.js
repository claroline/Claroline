import { API_REQUEST } from '#/main/app/api'

import { actions as formActions } from '#/main/app/content/form/store/actions'

import { selectors } from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
export const actions = {}

actions.save = (data) => (dispatch) => {
  return dispatch(formActions.save( selectors.STORE_NAME, (typeof data.id === 'undefined') ? ['apiv2_color_collection_create'] : ['apiv2_color_collection_update', { id: data.id }] ))
}
