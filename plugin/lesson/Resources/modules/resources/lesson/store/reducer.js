import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
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
  slug: '',
  title: '',
  text: '',
  parentSug: '',
  previousSlug: '',
  nextSlug: '',
  move: false,
  position: '',
  order: {}
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
    [CHAPTER_RESET]: () => ({})
  }),
  chapter_form: makeFormReducer(constants.CHAPTER_EDIT_FORM_NAME, formDefault, {
    data: makeReducer({}, {
      [CHAPTER_LOAD]: (state, action) => action.chapter,
      [CHAPTER_RESET]: () => ({}),
      [POSITION_SELECTED]: (state, action) => {
        const data = cloneDeep(state)
        data.position = action.isRoot ? 'subchapter' : data.position
        return data
      }
    })
  }),
  exportPdfEnabled: makeReducer(false, {}),
  tree: combineReducers({
    invalidated: makeReducer(false, {
      [TREE_LOADED]: () => false,
      [FORM_SUBMIT_SUCCESS + '/chapter_form']: () => true,
      [CHAPTER_DELETED]: () => true
    }),
    data: makeReducer({}, {
      [TREE_LOADED]: (state, action) => action.tree
    })
  })
}

export {
  reducer
}