import cloneDeep from 'lodash/cloneDeep'
import {makeReducer} from '#/main/core/utilities/redux'
import {
  CATEGORY_ADD,
  CATEGORY_UPDATE,
  CATEGORY_REMOVE
} from './actions'

const categoryReducers = makeReducer({}, {
  [CATEGORY_ADD]: (state, action) => {
    const categories = cloneDeep(state)
    categories.push(action.category)

    return categories
  },
  [CATEGORY_UPDATE]: (state, action) => {
    const categories = cloneDeep(state)
    const index = categories.findIndex(c => c.id === action.category.id)

    if (index >= 0) {
      categories[index] = action.category
    }

    return categories
  },
  [CATEGORY_REMOVE]: (state, action) => {
    const categories = cloneDeep(state)
    const index = categories.findIndex(c => c.id === action.categoryId)

    if (index >= 0) {
      categories.splice(index, 1)
    }

    return categories
  }
})

export {
  categoryReducers
}