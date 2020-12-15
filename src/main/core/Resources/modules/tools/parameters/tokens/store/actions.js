import {url} from '#/main/app/api/router'

import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/core/tools/parameters/store/selectors'

export const actions = {}

actions.create = () => ({
  [API_REQUEST]: {
    url: url(['apiv2_apitoken_create']),
    request: {
      method: 'POST',
      body: JSON.stringify({})
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'tokens.list'))
    }
  }
})

actions.open = (formName, id) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_apitoken_get', {id}],
        success: (response, dispatch) => dispatch(formActions.resetForm(formName, response, false))
      }
    }
  }
}
