import {url} from '#/main/core/api/router'
import {API_REQUEST} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

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
