import cloneDeep from 'lodash/cloneDeep'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {
  ANNOUNCE_DETAIL_OPEN,
  ANNOUNCE_DETAIL_RESET,
  ANNOUNCE_ADD,
  ANNOUNCE_DELETE,
  ANNOUNCE_CHANGE,
  ANNOUNCES_SORT_TOGGLE,
  ANNOUNCES_PAGE_CHANGE
} from './actions'

/**
 * Manages announcement posts sort (posts can only be ordered by date).
 * NB. 1 is for ASC, -1 is for DESC
 */
const sortReducer = makeReducer(-1, {
  [ANNOUNCES_SORT_TOGGLE]: (state) => 0-state
})

/**
 * Manages announcement pagination.
 */
const pageReducer = makeReducer(0, {
  [ANNOUNCES_PAGE_CHANGE]: (state, action) => action.page
})

/**
 * Manages announcements post CRUD actions.
 */
const announcementReducer = makeReducer({posts: []}, {
  [ANNOUNCE_ADD]: (state, action) => {
    const newState = cloneDeep(state)

    // add new announce to the list
    newState.posts.push(action.announce)

    return newState
  },
  [ANNOUNCE_CHANGE]: (state, action) => {
    const newState = cloneDeep(state)

    // update announce in the list
    const announcePos = newState.posts.findIndex(post => post.id === action.announce.id)

    if (announcePos > -1) {
      newState.posts[announcePos] = action.announce
    }

    return newState
  },
  [ANNOUNCE_DELETE]: (state, action) => {
    const newState = cloneDeep(state)

    // delete announce form the list
    newState.posts.splice(
      newState.posts.findIndex(post => post.id === action.announce.id),
      1
    )

    return newState
  }
})

const announcementDetailReducer = makeReducer(null, {
  [ANNOUNCE_DETAIL_OPEN]: (state, action) => action.announceId,
  [ANNOUNCE_DETAIL_RESET]: () => null
})

const reducer = {
  currentPage: pageReducer,
  sortOrder: sortReducer,
  announcement: announcementReducer,

  announcementForm: makeFormReducer('announcementForm'),
  announcementDetail: announcementDetailReducer,

  selected: combineReducers({
    list: makeListReducer('selected.list', {}, {
      invalidated: makeReducer(false, {
        ['MODAL_HIDE']: () => true // todo : find better
      })
    }, {
      selectable: false,
      filterable: true,
      readOnly: true,
      paginated: false,
      sortable: false
    })
  }),
  workspaceRoles: makeReducer({})
}

export {
  reducer
}
