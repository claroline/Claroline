import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store'

import {selectors} from '#/plugin/announcement/tools/announcement/store/selectors'
import {
  ANNOUNCE_DETAIL_OPEN,
  ANNOUNCE_DETAIL_RESET,
  ANNOUNCE_ADD,
  ANNOUNCE_DELETE,
  ANNOUNCE_CHANGE,
  ANNOUNCE_UPDATE_VIEWS
} from '#/plugin/announcement/tools/announcement/store/actions'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'

const reducer = combineReducers({
  parameters: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => !isEmpty(action.toolData.parameters) ? action.toolData.parameters : null
  }),
  posts: makeReducer([], {
    [CONTEXT_OPEN]: () => [],
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.posts || [],
    [ANNOUNCE_ADD]: (state, action) => {
      const newState = cloneDeep(state)

      // add new announce to the list
      newState.push(action.announce)

      return newState
    },
    [ANNOUNCE_CHANGE]: (state, action) => {
      const newState = cloneDeep(state)

      // update announce in the list
      const announcePos = newState.findIndex(post => post.id === action.announce.id)

      if (announcePos > -1) {
        newState[announcePos] = action.announce
      }

      return newState
    },
    [ANNOUNCE_DELETE]: (state, action) => {
      const newState = cloneDeep(state)

      // delete announce form the list
      newState.splice(
        newState.findIndex(post => post.id === action.announce.id),
        1
      )

      return newState
    },
    [ANNOUNCE_UPDATE_VIEWS]: (state, action) => {
      const newState = cloneDeep(state)
      const announcePos = newState.findIndex(post => post.id === action.announceId)
      newState[announcePos]['meta'].views = action.nbViews
      return newState
    }
  }),
  announcementDetail: makeReducer(null, {
    [ANNOUNCE_DETAIL_OPEN]: (state, action) => action.announceId,
    [ANNOUNCE_DETAIL_RESET]: () => null
  })
})

export {
  reducer
}
