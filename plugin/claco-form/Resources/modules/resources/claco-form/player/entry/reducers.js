import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {
  ENTRY_ADD,
  ENTRY_UPDATE,
  ENTRY_REMOVE,
  CURRENT_ENTRY_LOAD,
  CURRENT_ENTRY_UPDATE,
  ENTRY_COMMENT_ADD,
  ENTRY_COMMENT_UPDATE,
  ENTRY_COMMENT_REMOVE,
  ALL_ENTRIES_REMOVE
} from './actions'

const entriesReducers = makeReducer({}, {
  [ENTRY_ADD]: (state, action) => {
    const entries = cloneDeep(state)
    entries.push(action.entry)

    return entries
  },
  [ENTRY_UPDATE]: (state, action) => {
    const entries = cloneDeep(state)
    const index = entries.findIndex(c => c.id === action.entry.id)

    if (index >= 0) {
      entries[index] = action.entry
    }

    return entries
  },
  [ENTRY_REMOVE]: (state, action) => {
    const entries = cloneDeep(state)
    const index = entries.findIndex(c => c.id === action.entryId)

    if (index >= 0) {
      entries.splice(index, 1)
    }

    return entries
  },
  [ALL_ENTRIES_REMOVE]: () => {
    return []
  },
  [ENTRY_COMMENT_ADD]: (state, action) => {
    const entries = cloneDeep(state)
    const entryIndex = entries.findIndex(e => e.id === action.entryId)

    if (entryIndex >= 0) {
      const comments = [action.comment, ...entries[entryIndex].comments]
      entries[entryIndex] = Object.assign({}, entries[entryIndex], {comments: comments})

      return entries
    } else {
      return state
    }
  },
  [ENTRY_COMMENT_UPDATE]: (state, action) => {
    const entries = cloneDeep(state)
    const entryIndex = entries.findIndex(e => e.id === action.entryId)

    if (entryIndex >= 0) {
      const comments = cloneDeep(entries[entryIndex].comments)
      const commentIndex = comments.findIndex(c => c.id === action.comment.id)

      if (commentIndex >= 0) {
        comments[commentIndex] = action.comment
        entries[entryIndex] = Object.assign({}, entries[entryIndex], {comments: comments})
      }

      return entries
    } else {
      return state
    }
  },
  [ENTRY_COMMENT_REMOVE]: (state, action) => {
    const entries = cloneDeep(state)
    const entryIndex = entries.findIndex(e => e.id === action.entryId)

    if (entryIndex >= 0) {
      const comments = cloneDeep(entries[entryIndex].comments)
      const commentIndex = comments.findIndex(c => c.id === action.commentId)

      if (commentIndex >= 0) {
        comments.splice(commentIndex, 1)
        entries[entryIndex] = Object.assign({}, entries[entryIndex], {comments: comments})
      }

      return entries
    } else {
      return state
    }
  }
})

const myEntriesCountReducers = makeReducer({}, {
  [ENTRY_ADD]: (state) => {
    return state + 1
  }
})

const currentEntryReducers = makeReducer({}, {
  [CURRENT_ENTRY_LOAD]: (state, action) => {
    return action.entry
  },
  [CURRENT_ENTRY_UPDATE]: (state, action) => {
    return Object.assign({}, state, {[action.property]: action.value})
  },
  [ALL_ENTRIES_REMOVE]: () => {
    return {}
  },
  [ENTRY_COMMENT_ADD]: (state, action) => {
    if (state.id === action.entryId) {
      const comments = [action.comment, ...state.comments]

      return Object.assign({}, state, {comments: comments})
    } else {
      return state
    }
  },
  [ENTRY_COMMENT_UPDATE]: (state, action) => {
    if (state.id === action.entryId) {
      const comments = cloneDeep(state.comments)
      const index = comments.findIndex(c => c.id === action.comment.id)

      if (index >= 0) {
        comments[index] = action.comment
      }

      return Object.assign({}, state, {comments: comments})
    } else {
      return state
    }
  },
  [ENTRY_COMMENT_REMOVE]: (state, action) => {
    if (state.id === action.entryId) {
      const comments = cloneDeep(state.comments)
      const index = comments.findIndex(c => c.id === action.commentId)

      if (index >= 0) {
        comments.splice(index, 1)
      }

      return Object.assign({}, state, {comments: comments})
    } else {
      return state
    }
  }
})

const reducer = makeListReducer({data: entriesReducers}, {selectable: false})

export {
  reducer,
  entriesReducers,
  myEntriesCountReducers,
  currentEntryReducers
}