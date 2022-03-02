import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {
  CURRENT_TAB,
  ADMINISTRATION_SET,
  TABS_LOAD
} from '#/plugin/home/tools/home/store/actions'
import {selectors} from '#/plugin/home/tools/home/store/selectors'
import {reducer as editorReducer} from '#/plugin/home/tools/home/editor/store/reducer'
import {reducer as playerReducer} from '#/plugin/home/tools/home/player/store/reducer'

const reducer = combineReducers({
  administration: makeReducer(false, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.administration || false,
    [ADMINISTRATION_SET]: (state, action) => action.administration
  }),
  currentTabId: makeReducer(null, {
    [CURRENT_TAB]: (state, action) => action.tab
  }),

  tabs: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => {
      const tabs = [].concat(action.toolData.tabs || [])

      if (isEmpty(tabs) || -1 === tabs.findIndex(tab => !get(tab, 'restrictions.hidden', false))) {
        tabs.push(
          selectors.defaultTab({tool: {currentContext: action.context}})
        )
      }

      return tabs
    },
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.STORE_NAME + '.editor')]: (state, action) => action.updatedData,
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
  editor: editorReducer,
  player: playerReducer
})

export {
  reducer
}
