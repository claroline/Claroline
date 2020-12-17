import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {FORM_SUBMIT_SUCCESS, FORM_RESET} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/lesson/resources/lesson/store/selectors'
import {
  CHAPTER_LOAD,
  CHAPTER_RESET,
  TREE_LOADED,
  CHAPTER_DELETED,
  POSITION_SELECTED
} from '#/plugin/lesson/resources/lesson/store/actions'

import {reducer as editorReducer} from '#/plugin/lesson/resources/lesson/editor/store/reducer'
import {selectors as editorSelectors} from '#/plugin/lesson/resources/lesson/editor/store/selectors'

const reducer = combineReducers({
  lesson: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.lesson,
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, editorSelectors.FORM_NAME)]: (state, action) => action.updatedData
  }),
  editor: editorReducer,
  chapter: makeReducer({}, {
    [CHAPTER_LOAD]: (state, action) => action.chapter,
    [CHAPTER_RESET]: () => ({}),
    [CHAPTER_DELETED]: () => null
  }),
  chapters: makeListReducer(selectors.LIST_NAME),
  chapter_form: makeFormReducer(selectors.CHAPTER_EDIT_FORM_NAME, {}, {
    data: makeReducer({}, {
      [CHAPTER_LOAD]: (state, action) => Object.assign(cloneDeep(state), action.chapter),
      [CHAPTER_RESET]: () => ({
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
      }),
      [POSITION_SELECTED]: (state, action) => {
        const data = cloneDeep(state)
        data.position = action.isRoot ? 'subchapter' : data.position
        return data
      },
      [FORM_RESET + '/' + selectors.STORE_NAME + '.chapter_form']: (state, action) => Object.assign({
        position: 'subchapter',
        order: {
          sibling: 'before',
          subchapter: 'first'
        },
        parentSlug: state.parentSlug
      }, action.data || {})
    })
  }),
  tree: combineReducers({
    invalidated: makeReducer(false, {
      [TREE_LOADED]: () => false,
      [FORM_SUBMIT_SUCCESS + '/' + selectors.STORE_NAME + '.chapter_form']: () => true
    }),
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.tree,
      [TREE_LOADED]: (state, action) => action.tree,
      [CHAPTER_DELETED]: (state, action) => action.tree
    })
  }),
  root: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.root
  })
})

export {
  reducer
}
