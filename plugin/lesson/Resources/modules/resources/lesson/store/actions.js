import cloneDeep from 'lodash/cloneDeep'

import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'

export const SUMMARY_PIN_TOGGLE  = 'SUMMARY_PIN_TOGGLE'
export const SUMMARY_OPEN_TOGGLE = 'SUMMARY_OPEN_TOGGLE'

export const CHAPTER_LOAD      = 'CHAPTER_LOAD'
export const CHAPTER_RESET     = 'CHAPTER_RESET'
export const CHAPTER_CREATE    = 'CHAPTER_CREATE'
export const CHAPTER_EDIT      = 'CHAPTER_EDIT'
export const CHAPTER_DELETED   = 'CHAPTER_DELETED'
export const TREE_LOADED       = 'TREE_LOADED'
export const POSITION_SELECTED = 'POSITION_SELECTED'

export const actions = {}

actions.chapterLoad      = makeActionCreator(CHAPTER_LOAD, 'chapter')
actions.chapterReset     = makeActionCreator(CHAPTER_RESET)
actions.chapterCreate    = makeActionCreator(CHAPTER_CREATE)
actions.chapterEdit      = makeActionCreator(CHAPTER_EDIT)
actions.chapterDeleted   = makeActionCreator(CHAPTER_DELETED, 'tree')
actions.treeLoaded       = makeActionCreator(TREE_LOADED, 'tree')
actions.positionSelected = makeActionCreator(POSITION_SELECTED, 'isRoot')

actions.loadChapter = (lessonId, chapterSlug) => dispatch => {
  dispatch(actions.chapterReset())
  return dispatch({[API_REQUEST]: {
    url:['apiv2_lesson_chapter_get', {lessonId, chapterSlug}],
    success: (response, dispatch) => dispatch(actions.chapterLoad(response))
  }})
}

actions.editChapter = (formName, lessonId, chapterSlug) => dispatch => {
  dispatch(formActions.resetForm(formName, {}, false))
  dispatch(actions.chapterEdit())
  dispatch({[API_REQUEST]: {
    url: ['apiv2_lesson_chapter_get', {lessonId, chapterSlug}],
    success: (response, dispatch) => {
      dispatch(formActions.resetForm(formName, response, false))
      dispatch(actions.chapterLoad(response))
    }
  }})
}

actions.copyChapter = (formName, lessonId, chapterSlug) => dispatch => {
  dispatch(formActions.resetForm(formName, {}, true))
  dispatch(actions.chapterEdit())
  dispatch({[API_REQUEST]: {
    url: ['apiv2_lesson_chapter_get', {lessonId, chapterSlug}],
    success: (response, dispatch) => {
      dispatch(formActions.resetForm(formName, response, true))
      const data = cloneDeep(response)
      data.parentSlug = ''
      dispatch(actions.chapterLoad(data))
    }
  }})
}

actions.createChapter = formName => dispatch => {
  dispatch(actions.chapterReset())
  dispatch(formActions.resetForm(formName, {}, true))
  dispatch(actions.chapterCreate())
}

actions.deleteChapter = (lessonId, chapterSlug, deleteChildren = false) => dispatch =>
  dispatch({[API_REQUEST]: {
    url: ['apiv2_lesson_chapter_delete', {lessonId, chapterSlug}],
    request: {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        deleteChildren: deleteChildren
      })
    },
    success: (response, dispatch) => {
      dispatch(actions.chapterDeleted(response.tree))
    }
  }})

actions.fetchChapterTree = lessonId => dispatch => {
  dispatch({[API_REQUEST]: {
    url: ['apiv2_lesson_tree_get', {lessonId}],
    success: (response, dispatch) => dispatch(actions.treeLoaded(response))
  }})
}

actions.positionChange = value => (dispatch, getState) => {
  dispatch(actions.positionSelected(value === getState().tree.data.slug))
}
