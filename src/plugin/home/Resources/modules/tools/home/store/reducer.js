import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {TOOL_LOAD, TOOL_OPEN} from '#/main/core/tool/store/actions'

import {
  CURRENT_TAB,
  TAB_LOAD,
  TAB_SET_LOADED,
  TAB_SET_ERROR,
  TAB_UPDATE_VIEWS
} from '#/plugin/home/tools/home/store/actions'
import {selectors} from '#/plugin/home/tools/home/store/selectors'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'

const reducer = combineReducers({
  currentTabId: makeReducer(null, {
    [CONTEXT_OPEN]: () => null,
    [TOOL_OPEN]: () => null,
    [CURRENT_TAB]: (state, action) => action.tab
  }),

  tabs: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => {
      const tabs = [].concat(action.toolData.tabs || [])

      if (isEmpty(tabs) || -1 === tabs.findIndex(tab => !get(tab, 'restrictions.hidden', false))) {
        tabs.push(
          selectors.defaultTab({tool: {currentContext: action.context}})
        )
      }

      return tabs
    },
    [TAB_UPDATE_VIEWS]: (state, action) => {
      const newState = cloneDeep(state)
      const tab = state.findIndex(tab => tab.id === action.tabId)
      newState[tab]['meta'].views = action.nbViews
      return newState
    }
  }),

  loaded: makeReducer(false, {
    [CONTEXT_OPEN]: () => false,
    [SECURITY_USER_CHANGE]: () => false,
    [TAB_LOAD]: () => true,
    [TAB_SET_LOADED]: (state, action) => action.loaded,
    [TAB_SET_ERROR]: () => true
  }),

  current: makeReducer(null, {
    [CONTEXT_OPEN]: () => null,
    [TAB_LOAD]: (state, action) => action.homeTab
  }),

  error: makeReducer(null, {
    [TAB_LOAD]: (state, action) => action.error || null,
    [TAB_SET_ERROR]: (state, action) => ({
      code: action.code.toUpperCase(),
      message: action.message,
      additional: action.additional
    })
  })
})

export {
  reducer
}
