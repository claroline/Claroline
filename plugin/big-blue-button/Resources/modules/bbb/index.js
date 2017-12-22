import React from 'react'
import cloneDeep from 'lodash/cloneDeep'
import {
  hashHistory as history,
  HashRouter as Router
} from 'react-router-dom'
import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {reducer as modalReducer}    from '#/main/core/layout/modal/reducer'
import {reducer as resourceNodeReducer} from '#/main/core/layout/resource/reducer'
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

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  () => React.createElement(Router, {
    history: history
  }, React.createElement(BBBResource)),

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
    message: messageReducers,

    // generic reducers
    resourceNode: resourceNodeReducer,
    modal: modalReducer
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
      canEdit: resourceNode.rights.current.edit,
      canJoin: resourceNode.rights.current.edit || !resource.moderatorRequired,
      bbbUrl: null
    }
  }
)
