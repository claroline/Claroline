import {API_REQUEST} from '#/main/app/api'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {selectors} from '#/main/theme/administration/appearance/modals/icon-set-creation/store/selectors'

export const actions = {}

actions.save = (data) => (dispatch) => {
  const formData = new FormData()
  formData.append('name', data.name || null)
  formData.append('archive', data.archive) // this is an uploaded file

  dispatch(formActions.submit(selectors.STORE_NAME))

  return dispatch({
    [API_REQUEST]: {
      url: ['apiv2_icon_set_create'],
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      error: (errors) => dispatch(formActions.errors(selectors.STORE_NAME, errors))
    }
  })
}
