import cloneDeep from 'lodash/cloneDeep'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer} from '#/main/app/store/reducer'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store/selectors'
import {
  CATEGORY_ADD,
  CATEGORY_UPDATE,
  KEYWORD_ADD,
  KEYWORD_UPDATE
} from '#/plugin/claco-form/resources/claco-form/editor/store/actions'

const reducer = makeFormReducer(selectors.STORE_NAME+'.clacoFormForm', {}, {
  data: makeReducer({}, {
    [CATEGORY_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState['categories'].push(action.category)

      return newState
    },
    [CATEGORY_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState['categories'].findIndex(c => c.id === action.category.id)

      if (index >= 0) {
        newState['categories'][index] = action.category
      }

      return newState
    },
    [KEYWORD_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      newState['keywords'].push(action.keyword)

      return newState
    },
    [KEYWORD_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const index = newState['keywords'].findIndex(k => k.id === action.keyword.id)

      if (index >= 0) {
        newState['keywords'][index] = action.keyword
      }

      return newState
    }
  }),
  categories: makeListReducer(selectors.STORE_NAME+'.clacoFormForm.categories', {}, {
    data: makeReducer({}, {
      [CATEGORY_UPDATE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState.findIndex(c => c.id === action.category.id)

        if (index >= 0) {
          newState[index] = action.category
        }

        return newState
      }
    }),
    invalidated: makeReducer(false, {
      [CATEGORY_ADD]: () => true
    })
  }),
  keywords: makeListReducer(selectors.STORE_NAME+'.clacoFormForm.keywords', {}, {
    data: makeReducer({}, {
      [KEYWORD_UPDATE]: (state, action) => {
        const newState = cloneDeep(state)
        const index = newState.findIndex(k => k.id === action.keyword.id)

        if (index >= 0) {
          newState[index] = action.keyword
        }

        return newState
      }
    }),
    invalidated: makeReducer(false, {
      [KEYWORD_ADD]: () => true
    })
  })
})

export {
  reducer
}