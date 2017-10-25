import merge from 'lodash/merge'

import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {generateUrl} from '#/main/core/fos-js-router'

// reducers
import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducer} from '#/main/core/administration/workspace/reducer'


import {t, transChoice} from '#/main/core/translation'

import {Workspaces} from '#/main/core/administration/workspace/components/workspaces.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Workspaces,

  // app store configuration
  {
    // app reducers
    workspaces: reducer,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer
  },

  // remap data-attributes set on the app DOM container
  (initialData) => ({
    workspaces: merge({}, initialData.workspaces, {
      fetchUrl: generateUrl('apiv2_workspace_list'),
      delete: {
        title: (workspaces) => transChoice('remove_workspaces', workspaces.length, {count: workspaces.length}, 'platform'),
        question: (workspaces) => t('remove_workspaces_confirm', {
          workspace_list: workspaces.map(workspace => workspace.name).join(', ')
        }),
        displayed: (workspaces) => {
          return 0 < workspaces.filter(workspace => workspace.code !== 'default_personal' && workspace.code !== 'default_workspace' ).length
        }
      }
    })
  })
)
