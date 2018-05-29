import {makeActionCreator} from '#/main/core/scaffolding/actions'

import {API_REQUEST} from '#/main/app/api'

import {selectors} from '#/main/core/resource/store/selectors'

// actions
export const RESOURCE_UPDATE_NODE          = 'RESOURCE_UPDATE_NODE'
export const RESOURCE_UPDATE_PUBLICATION   = 'RESOURCE_UPDATE_PUBLICATION'
export const RESOURCE_UPDATE_NOTIFICATIONS = 'RESOURCE_UPDATE_NOTIFICATIONS'

export const USER_EVALUATION_UPDATE = 'USER_EVALUATION_UPDATE'

// action creators
export const actions = {}

actions.update              = makeActionCreator(RESOURCE_UPDATE_NODE, 'resourceNode')

// todo : remove me (use resource action system)
actions.updatePublication   = makeActionCreator(RESOURCE_UPDATE_PUBLICATION)

// todo : remove me (use resource action system)
actions.updateNotifications = makeActionCreator(RESOURCE_UPDATE_NOTIFICATIONS)

actions.triggerLifecycleAction = (action) => (dispatch, getState) => {
  const lifecycleActions = selectors.resourceLifecycle(getState())

  // checks if the current resource implements the action
  if (lifecycleActions[action]) {
    // dispatch the implemented action with resourceNode as param (don't know if this is useful)
    return lifecycleActions[action](
      selectors.resourceNode(getState())
    )
  }
}

// todo : remove me (use resource action system)
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

// todo : remove me (use resource action system)
actions.togglePublication = (resourceNode) => ({
  [API_REQUEST]: {
    type: resourceNode.meta.published ? 'unpublish' : 'publish',
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

// todo : remove me (use resource action system)
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

actions.updateUserEvaluation = makeActionCreator(USER_EVALUATION_UPDATE, 'userEvaluation')
