import {generateUrl} from '#/main/core/api/router'
import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {API_REQUEST} from '#/main/core/api/actions'

const RESOURCE_TYPE_ADD = 'RESOURCE_TYPE_ADD'
const RESOURCE_TYPE_UPDATE = 'RESOURCE_TYPE_UPDATE'
const RESOURCE_TYPE_REMOVE = 'RESOURCE_TYPE_REMOVE'

const actions = {}

actions.addResourceType = makeActionCreator(RESOURCE_TYPE_ADD, 'resourceType')
actions.updateResourceType = makeActionCreator(RESOURCE_TYPE_UPDATE, 'resourceType')
actions.removeResourceType = makeActionCreator(RESOURCE_TYPE_REMOVE, 'id')

actions.saveResourceType = (resourceType) => (dispatch) => {
  if (resourceType.id) {
    dispatch({
      [API_REQUEST]: {
        url: generateUrl('apiv2_reservationresourcetype_update', {id: resourceType.id}),
        request: {
          method: 'PUT',
          body: JSON.stringify(resourceType)
        },
        success: (data, dispatch) => {
          dispatch(actions.updateResourceType(data))
        }
      }
    })
  } else {
    dispatch({
      [API_REQUEST]: {
        url: generateUrl('apiv2_reservationresourcetype_create'),
        request: {
          method: 'POST',
          body: JSON.stringify(resourceType)
        },
        success: (data, dispatch) => {
          dispatch(actions.addResourceType(data))
        }
      }
    })
  }
}

actions.deleteResourceType = (resourceTypeId) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_reservationresourcetype_delete_bulk') + '?ids[]=' + resourceTypeId,
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeResourceType(resourceTypeId))
    }
  }
})

export {
  actions,
  RESOURCE_TYPE_ADD,
  RESOURCE_TYPE_UPDATE,
  RESOURCE_TYPE_REMOVE
}