import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {getTabParent, getFormDataPart} from '#/plugin/home/tools/home/editor/utils'
import {TABS_LOAD} from '#/plugin/home/tools/home/store/actions'
import {selectors as baseSelectors} from '#/plugin/home/tools/home/store/selectors'
import {HOME_MOVE_TAB} from '#/plugin/home/tools/home/editor/store/actions'
import {selectors} from '#/plugin/home/tools/home/editor/store/selectors'

function pushTab(tab, tabs, position) {
  const newTabs = cloneDeep(tabs)

  switch (position.order) {
    case 'first':
      newTabs.unshift(tab)
      break

    case 'before':
    case 'after':
      if ('before' === position.order) {
        newTabs.splice(tabs.findIndex(t => t.id === position.tab), 0, tab)
      } else {
        newTabs.splice(tabs.findIndex(t => t.id === position.tab) + 1, 0, tab)
      }
      break

    case 'last':
      newTabs.push(tab)
      break
  }

  return newTabs
    // recompute tabs positions
    .map((tab, index) => merge({}, tab, {
      position: index + 1
    }))
}

const reducer = makeFormReducer(selectors.FORM_NAME, {data: [], originalData: []}, {
  validating: makeReducer(false, {
    [HOME_MOVE_TAB]: () => false
  }),
  pendingChanges: makeReducer(false, {
    [HOME_MOVE_TAB]: () => true
  }),
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
    },
    [HOME_MOVE_TAB]: (state, action) => {
      let newState = cloneDeep(state)

      // get the tab to move
      const original = get(newState, getFormDataPart(action.id, newState))

      // remove the tab from its current position
      const parent = getTabParent(action.id, newState)
      if (parent) {
        const currentPos = parent.children.findIndex(child => child.id === action.id)
        parent.children.splice(currentPos, 1)
      } else {
        const currentPos = newState.findIndex(child => child.id === action.id)
        newState.splice(currentPos, 1)
      }

      // move the tab at the new position
      if (action.position.parent) {
        const parent = get(newState, getFormDataPart(action.position.parent, newState))

        parent.children = pushTab(original, parent.children, action.position)
      } else {
        newState = pushTab(original, newState, action.position)
      }

      return newState
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
