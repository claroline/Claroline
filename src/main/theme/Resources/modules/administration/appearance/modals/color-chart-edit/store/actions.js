import {API_REQUEST} from '#/main/app/api'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-edit/store/selectors'
export const actions = {}

actions.save = (data) => (dispatch) => {
  dispatch(formActions.submit(selectors.STORE_NAME))
  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_color_collection_update', {id: data.id}],
      request: {
        method: 'PUT',
        body: JSON.stringify(data),
        headers: new Headers({
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        })
      },
      error: (errors) => dispatch(formActions.errors(selectors.STORE_NAME, errors))
    }
  })
}
