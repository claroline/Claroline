import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {TOOL_LOAD, TOOL_OPEN} from '#/main/core/tool/store/actions'

import {
  CURRENT_TAB,
  TABS_LOAD,
  TAB_LOAD,
  TAB_RESTRICTIONS_DISMISS,
  TAB_SET_LOADED
} from '#/plugin/home/tools/home/store/actions'
import {selectors} from '#/plugin/home/tools/home/store/selectors'

const reducer = combineReducers({
  currentTabId: makeReducer(null, {
    [TOOL_OPEN]: () => null,
    [CURRENT_TAB]: (state, action) => action.tab
  }),

  tabs: makeReducer([], {
    [TOOL_OPEN]: () => [],
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => {
      const tabs = [].concat(action.toolData.tabs || [])

      if (isEmpty(tabs) || -1 === tabs.findIndex(tab => !get(tab, 'restrictions.hidden', false))) {
        tabs.push(
          selectors.defaultTab({tool: {currentContext: action.context}})
        )
      }

      return tabs
    },
    [TABS_LOAD]: (state, action) => {
      const tabs = [].concat(action.tabs || [])

      if (isEmpty(tabs) || -1 === tabs.findIndex(tab => !get(tab, 'restrictions.hidden', false))) {
        tabs.push(
          selectors.defaultTab({tool: {currentContext: action.context}})
        )
      }

      return tabs
    }
  }),

  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [TAB_LOAD]: () => true,
    [TAB_SET_LOADED]: (state, action) => action.loaded
  }),

  current: makeReducer(null, {
    [TAB_LOAD]: (state, action) => action.homeTab
  }),

  managed: makeReducer(false, {
    [TAB_LOAD]: (state, action) => action.managed || false
  }),

  accessErrors: combineReducers({
    dismissed: makeReducer(false, {
      [TAB_RESTRICTIONS_DISMISS]: () => true,
      [TAB_LOAD]: () => false
    }),
    details: makeReducer({}, {
      [TAB_LOAD]: (state, action) => action.accessErrors || {}
    })
  })
})

export {
  reducer
}
