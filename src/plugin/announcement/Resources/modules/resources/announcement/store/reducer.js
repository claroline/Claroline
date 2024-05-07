import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/announcement/resources/announcement/store/selectors'
import {
  ANNOUNCE_DETAIL_OPEN,
  ANNOUNCE_DETAIL_RESET,
  ANNOUNCE_ADD,
  ANNOUNCE_DELETE,
  ANNOUNCE_CHANGE,
  ANNOUNCES_SORT_TOGGLE,
  ANNOUNCES_PAGE_CHANGE
} from '#/plugin/announcement/resources/announcement/store/actions'

const reducer = combineReducers({
  /**
   * Manages announcement pagination.
   */
  currentPage: makeReducer(0, {
    [ANNOUNCES_PAGE_CHANGE]: (state, action) => action.page
  }),

  /**
   * Manages announcement posts sort (posts can only be ordered by date).
   * NB. 1 is for ASC, -1 is for DESC
   */
  sortOrder: makeReducer(-1, {
    [ANNOUNCES_SORT_TOGGLE]: (state) => 0-state
  }),

  announcement: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.announcement
  }),

  posts: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.posts,
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
    }
  }),
  announcementForm: makeFormReducer(selectors.STORE_NAME+'.announcementForm'),
  announcementDetail: makeReducer(null, {
    [ANNOUNCE_DETAIL_OPEN]: (state, action) => action.announceId,
    [ANNOUNCE_DETAIL_RESET]: () => null
  }),

  workspaceRoles: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.workspaceRoles
  })
})

export {
  reducer
}
