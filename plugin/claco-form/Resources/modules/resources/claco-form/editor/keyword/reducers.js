import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  KEYWORD_ADD,
  KEYWORD_UPDATE,
  KEYWORD_REMOVE
} from './actions'

const keywordReducers = makeReducer({}, {
  [KEYWORD_ADD]: (state, action) => {
    const keywords = cloneDeep(state)
    keywords.push(action.keyword)

    return keywords
  },
  [KEYWORD_UPDATE]: (state, action) => {
    const keywords = cloneDeep(state)
    const index = keywords.findIndex(k => k.id === action.keyword.id)

    if (index >= 0) {
      keywords[index] = action.keyword
    }

    return keywords
  },
  [KEYWORD_REMOVE]: (state, action) => {
    const keywords = cloneDeep(state)
    const index = keywords.findIndex(k => k.id === action.keywordId)

    if (index >= 0) {
      keywords.splice(index, 1)
    }

    return keywords
  }
})

export {
  keywordReducers
}