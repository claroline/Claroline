import {makeActionCreator} from '#/main/core/scaffolding/actions'

import {API_REQUEST} from '#/main/core/api/actions'

export const RESOURCE_UPDATE_NODE        = 'RESOURCE_UPDATE_NODE'
export const RESOURCE_UPDATE_PUBLICATION = 'RESOURCE_UPDATE_PUBLICATION'
export const RESOURCE_UPDATE_NOTIFICATIONS = 'RESOURCE_UPDATE_NOTIFICATIONS'

export const actions = {}

actions.update              = makeActionCreator(RESOURCE_UPDATE_NODE, 'resourceNode')
actions.updatePublication   = makeActionCreator(RESOURCE_UPDATE_PUBLICATION)
actions.updateNotifications = makeActionCreator(RESOURCE_UPDATE_NOTIFICATIONS)

actions.updateNode = (resourceNode) => ({
  [API_REQUEST]: {
    url: ['claro_resource_node_update', {id: resourceNode.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(resourceNode)
    },
    success: (data, dispatch) => dispatch(actions.update(data))
  }
})

actions.togglePublication = (resourceNode) => ({
  [API_REQUEST]: {
    url: [
      resourceNode.meta.published ? 'claro_resource_node_unpublish' : 'claro_resource_node_publish',
      {id: resourceNode.id}
    ],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.updatePublication())
  }
})

actions.toggleNotifications = (resourceNode) => ({
  [API_REQUEST]: {
    url: [
      resourceNode.notifications.enabled ? 'icap_notification_resource_disable' : 'icap_notification_resource_enable',
      {resourceId: resourceNode.autoId, resourceClass: window.btoa(resourceNode.meta.class)}
    ],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => dispatch(actions.updateNotifications())
  }
})
