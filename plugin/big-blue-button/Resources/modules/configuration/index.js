import React from 'react'
import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {
  mainReducers,
  configReducers,
  messageReducers,
  meetingsReducers
} from './reducers'
import {BBBConfigForm} from './components/bbb-config-form.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.bbb-config-container',

  // app main component
  () =>  React.createElement(BBBConfigForm),

  // app store configuration
  {
    // app reducers
    user: mainReducers,
    config: configReducers,
    message: messageReducers,
    meetings: meetingsReducers
  },

  // transform data attributes for redux store
  (initialData) => {
    return {
      user: initialData.user,
      config: {
        serverUrl: initialData.serverUrl,
        securitySalt: initialData.securitySalt
      }
    }
  }
)