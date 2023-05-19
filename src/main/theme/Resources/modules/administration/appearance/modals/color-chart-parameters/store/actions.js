import { API_REQUEST } from '#/main/app/api'

import { actions as formActions } from '#/main/app/content/form/store/actions'

import { selectors } from '#/main/theme/administration/appearance/modals/color-chart-parameters/store/selectors'
export const actions = {}

actions.save = (data) => (dispatch) => {
  const isNew = typeof data.id === 'undefined'

  return dispatch({
    [API_REQUEST]: {
      url: isNew ? ['apiv2_color_collection_create'] : ['apiv2_color_collection_update', { id: data.id }],
      request: {
        method: isNew ? 'POST' : 'PUT',
        body: JSON.stringify(data)
      },
      error: (errors) => dispatch(formActions.errors(selectors.STORE_NAME, errors))
    }
  })
}

