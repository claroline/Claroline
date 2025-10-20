import {TOOL_LOAD} from '#/main/core/tool/store'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/authentication/administration/authentication/store/selectors'
import {makeListReducer} from '#/main/app/content/list/store'
import {
  AUTHENTICATION_OAUTH_CLIENT_ADD, AUTHENTICATION_OAUTH_CLIENT_DELETE,
  AUTHENTICATION_OAUTH_CLIENT_UPDATE
} from '#/main/authentication/administration/authentication/store/actions'
import cloneDeep from 'lodash/cloneDeep'

export const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.authentication
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.authentication
    })
  }),
  oauthProviders: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.oauthProviders
  }),
  oauthClients: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.oauthClients,
    [AUTHENTICATION_OAUTH_CLIENT_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState.push(action.client)

      return newState
    },
    [AUTHENTICATION_OAUTH_CLIENT_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = state.findIndex(client => client.id === action.client.id)
      if (-1 !== pos) {
        newState.splice(pos, 1, action.client)
      }

      return newState
    },
    [AUTHENTICATION_OAUTH_CLIENT_DELETE]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = state.findIndex(client => client.id === action.client.id)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      return newState
    }
  }),
  ips: makeListReducer(selectors.STORE_NAME+'.ips'),
  tokens: makeListReducer(selectors.STORE_NAME+'.tokens')
})
