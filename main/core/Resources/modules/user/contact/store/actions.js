import {url} from '#/main/app/api'
import {API_REQUEST} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

actions.createContacts = users => ({
  [API_REQUEST]: {
    url: url(['apiv2_contacts_create'], {ids: users}),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('contacts'))
    }
  }
})
