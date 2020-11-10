import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {TABS_LOAD} from '#/plugin/home/tools/home/store/actions'
import {selectors as baseSelectors} from '#/plugin/home/tools/home/store/selectors'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {data: [], originalData: []}, {
  data: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => {
      if (!isEmpty(action.toolData.tabs)) {
        return action.toolData.tabs
      }

      return [
        baseSelectors.defaultTab({tool: {currentContext: action.context}})
      ]
    },
    [TABS_LOAD]: (state, action) => {
      if (!isEmpty(action.tabs)) {
        return action.tabs
      }

      return [
        baseSelectors.defaultTab({tool: {currentContext: action.context}})
      ]
    }
  }),
  originalData: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => {
      if (!isEmpty(action.toolData.tabs)) {
        return action.toolData.tabs
      }

      return [
        baseSelectors.defaultTab({tool: {currentContext: action.context}})
      ]
    },
    [TABS_LOAD]: (state, action) => {
      if (!isEmpty(action.tabs)) {
        return action.tabs
      }

      return [
        baseSelectors.defaultTab({tool: {currentContext: action.context}})
      ]
    }
  })
})

export {
  reducer
}
