import {makeReducer} from '#/main/app/store/reducer'

import {
  TAGS_LOAD,
  TAG_ADD,
  TAG_DELETE
} from '#/plugin/tag/modals/tags/store/actions'

const reducer = makeReducer([], {
  [TAGS_LOAD]: (state, action) => action.tags || [],
  [TAG_ADD]: (state, action) => {
    const newState = state.slice(0)

    newState.push(action.tag)

    return newState
  },
  [TAG_DELETE]: (state, action) => {
    const newState = state.slice(0)

    const pos = newState.findIndex(tag => tag.id === action.tag.id)
    if (-1 !== pos) {
      newState.splice(pos, 1)
    }

    return newState
  }
})

export {
  reducer
}