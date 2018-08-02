import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS, FORM_RESET} from '#/main/app/content/form/store/actions'
import {
  SUMMARY_PIN_TOGGLE,
  SUMMARY_OPEN_TOGGLE
} from '#/plugin/path/resources/path/actions'
import {
  CHAPTER_LOAD,
  CHAPTER_RESET,
  TREE_LOADED,
  CHAPTER_DELETED,
  POSITION_SELECTED
} from '#/plugin/lesson/resources/lesson/store/actions'
import {constants} from '#/plugin/lesson/resources/lesson/constants'

const formDefault = {
  id: null,
  slug: null,
  title: null,
  text: null,
  parentSug: null,
  previousSlug: null,
  nextSlug: null,
  move: false,
  position: null,
  order: {
    sibling: null,
    subchapter: null
  }
}

const reducer = {
  summary: combineReducers({
    pinned: makeReducer(false, {
      [SUMMARY_PIN_TOGGLE]: (state) => !state
    }),
    opened: makeReducer(false, {
      [SUMMARY_OPEN_TOGGLE]: (state) => !state
    })
  }),
  lesson: makeReducer({}, {}),
  chapter: makeReducer({}, {
    [CHAPTER_LOAD]: (state, action) => action.chapter,
    [CHAPTER_RESET]: () => ({}),
    [CHAPTER_DELETED]: () => null
  }),
  chapter_form: makeFormReducer(constants.CHAPTER_EDIT_FORM_NAME, {}, {
    data: makeReducer({}, {
      [CHAPTER_LOAD]: (state, action) => Object.assign(cloneDeep(state), action.chapter),
      [CHAPTER_RESET]: () => (formDefault),
      [POSITION_SELECTED]: (state, action) => {
        const data = cloneDeep(state)
        data.position = action.isRoot ? 'subchapter' : data.position
        return data
      },
      [FORM_RESET + '/chapter_form']: () => ({
        position: 'subchapter',
        order: {
          sibling: 'before',
          subchapter: 'first'
        }
      })
    })
  }),
  exportPdfEnabled: makeReducer(false, {}),
  tree: combineReducers({
    invalidated: makeReducer(false, {
      [TREE_LOADED]: () => false,
      [FORM_SUBMIT_SUCCESS + '/chapter_form']: () => true
    }),
    data: makeReducer({}, {
      [TREE_LOADED]: (state, action) => action.tree,
      [CHAPTER_DELETED]: (state, action) => action.tree
    })
  })
}

export {
  reducer
}