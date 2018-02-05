import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {generateUrl} from '#/main/core/api/router'
import {API_REQUEST} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

export const OPTIONS_LOAD = 'OPTIONS_LOAD'

export const actions = {}

actions.loadOptions = makeActionCreator(OPTIONS_LOAD, 'options')

actions.saveOptions = options => ({
  [API_REQUEST]: {
    url: ['apiv2_contact_options_update', {id: options.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(options)
    },
    success: (data, dispatch) => {
      dispatch(actions.loadOptions(data))
    }
  }
})

actions.createContacts = users => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_contacts_create') +'?'+ users.map(u => 'ids[]='+u).join('&'),
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('contacts'))
      dispatch(listActions.invalidateData('users.picker'))
    }
  }
})
