import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/lesson/resources/lesson/store/selectors'
import {
  LESSON_SEARCH,
  LESSON_PAGE_ADD,
  LESSON_PAGE_UPDATE, LESSON_PAGES_REFRESH,
  LESSON_SET_CURRENT_PAGE
} from '#/plugin/lesson/resources/lesson/store/actions'
import cloneDeep from 'lodash/cloneDeep'

const reducer = combineReducers({
  currentSearch: makeReducer('', {
    [LESSON_SEARCH]: (state, action) => action.search
  }),
  currentPage: makeReducer(null, {
    [LESSON_SET_CURRENT_PAGE]: (state, action) => action.pageSlug
  }),
  chapters: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.chapters || state,
    [LESSON_PAGES_REFRESH]: (state, action) => action.pages || state,
    [LESSON_PAGE_ADD]: (state, action) => {
      const newState = cloneDeep(state)

      const previousPos = state.findIndex(page => page.slug === action.page.previousSlug)

      newState[previousPos].nextSlug = action.page.slug

      if (newState[previousPos + 1]) {
        newState[previousPos + 1].previousSlug = action.page.slug
      }

      newState.splice(previousPos + 1, 0, action.page)

      return newState
    },
    [LESSON_PAGE_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)

      const pos = state.findIndex(page => page.slug === action.page.slug)
      if (-1 !== pos) {
        newState[pos] = action.page
      }

      return newState
    }
  }),
  placeholders: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.placeholders || state
  }),
  chapterForm: makeFormReducer(selectors.CHAPTER_FORM_NAME)
})

export {
  reducer
}
