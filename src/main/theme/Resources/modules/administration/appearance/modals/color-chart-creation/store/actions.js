import {API_REQUEST} from '#/main/app/api'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/theme/administration/appearance/modals/color-chart-creation/store/selectors'

export const actions = {}

actions.save = (data) => (dispatch) => {

  dispatch(formActions.submit(selectors.STORE_NAME))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_color_chart_create_update'],
      request: {
        method: 'POST',
        body: JSON.stringify(data),
        headers: new Headers({
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        })
      },
    }
  })
}
