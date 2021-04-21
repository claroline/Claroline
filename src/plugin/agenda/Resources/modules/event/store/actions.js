import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.delete = (event) => ({
  [API_REQUEST]: {
    url: ['apiv2_planned_object_delete_bulk', {ids: [event.id]}],
    request: {
      method: 'DELETE'
    }
  }
})
