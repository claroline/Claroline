// import {url} from '#/main/app/api'

import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
// import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_cursus_course_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

// actions.addUsers = (id, users) => ({
//   [API_REQUEST]: {
//     url: url(['apiv2_role_add_users', {id: id}], {ids: users}),
//     request: {
//       method: 'PATCH'
//     },
//     success: (data, dispatch) => {
//       dispatch(listActions.invalidateData('roles.list'))
//       dispatch(listActions.invalidateData('roles.current.users'))
//     }
//   }
// })
