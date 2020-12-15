import {url} from '#/main/app/api'

import {now} from '#/main/app/intl/date'
import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/administration/scheduled-task/store/selectors'

export const actions = {}

actions.open = (formName, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_scheduledtask_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, {
      scheduledDate: now(false)
    }, true))
  }
}

actions.addUsers = (id, users) => ({
  [API_REQUEST]: {
    url: url(['apiv2_scheduledtask_add_users', {id: id}], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.task'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME + '.task.users'))
    }
  }
})
