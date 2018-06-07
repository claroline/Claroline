import cloneDeep from 'lodash/cloneDeep'

import {bootstrap} from '#/main/app/bootstrap'
import {hasPermission} from '#/main/core/resource/permissions'

import {
  bbbReducers,
  resourceFormReducers,
  resourceReducers,
  mainReducers,
  canJoinReducers,
  messageReducers
} from './reducers'
import {BBBResource} from './components/bbb-resource.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.bbb-container',

  // app main component
  BBBResource,

  // app store configuration
  {
    // app reducers
    user: mainReducers,
    resourceForm: resourceFormReducers,
    resource: resourceReducers,
    bbbUrl: bbbReducers,
    config: mainReducers,
    canEdit: mainReducers,
    canJoin: canJoinReducers,
    message: messageReducers
  },

  // transform data attributes for redux store
  (initialData) => {
    const resourceNode = initialData.resourceNode
    const resource = initialData.resource
    const resourceForm = cloneDeep(resource)
    resourceForm['startDate'] = resourceForm['startDate'] ? new Date(resourceForm['startDate'].date) : resourceForm['startDate']
    resourceForm['endDate'] = resourceForm['endDate'] ? new Date(resourceForm['endDate'].date) : resourceForm['endDate']

    return {
      user: initialData.user,
      resourceForm: resourceForm,
      resource: resource,
      resourceNode: resourceNode,
      config: {
        serverUrl: initialData.serverUrl,
        securitySalt: initialData.securitySalt
      },
      canEdit: hasPermission('edit', resourceNode),
      canJoin: hasPermission('edit', resourceNode) || !resource.moderatorRequired,
      bbbUrl: null
    }
  }
)
