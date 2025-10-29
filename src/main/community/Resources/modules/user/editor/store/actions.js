import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/user/editor/store/selectors'

export const actions = {}

actions.reset = (user) => formActions.resetForm(selectors.FORM_NAME, user, false)

actions.open = (username) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_user_get', {field: 'username', id: username}],
    silent: true,
    success: (response) => dispatch(actions.reset(response))
  }
})

actions.addRoles = (id, roles) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_user_add_roles', {id: id}],
    request: {
      method: 'PATCH',
      body: JSON.stringify(roles)
    },
    success: () => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.roles'))
    }
  }
})
