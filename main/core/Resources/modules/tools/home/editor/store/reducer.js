import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {TABS_LOAD} from '#/main/core/tools/home/store/actions'
import {selectors as baseSelectors} from '#/main/core/tools/home/store/selectors'
import {selectors} from '#/main/core/tools/home/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {data: [], originalData: []}, {
  data: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'home')]: (state, action) => {
      if (!isEmpty(action.toolData.tabs)) {
        return action.toolData.tabs
      }

      return [
        baseSelectors.defaultTab({tool: {currentContext: action.context}})
      ]
    },
    [TABS_LOAD]: (state, action) => action.tabs
  }),
  originalData: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'home')]: (state, action) => {
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
