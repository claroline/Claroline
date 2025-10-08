import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {actions as listActions} from '#/main/app/content/list/store/actions'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as resourceActions} from '#/main/core/resource/store'

import {selectors} from '#/plugin/lesson/resources/lesson/store/selectors'

export const LESSON_SET_CURRENT_PAGE = 'LESSON_SET_CURRENT_PAGE'
export const LESSON_PAGE_ADD = 'LESSON_PAGE_ADD'
export const LESSON_PAGE_UPDATE = 'LESSON_PAGE_UPDATE'
export const LESSON_PAGES_REFRESH = 'LESSON_PAGES_REFRESH'

export const actions = {}

actions.setCurrentPage = makeActionCreator(LESSON_SET_CURRENT_PAGE, 'pageSlug')
actions.addPage = makeActionCreator(LESSON_PAGE_ADD, 'page')
actions.updatePage = makeActionCreator(LESSON_PAGE_UPDATE, 'page')
actions.refreshPages = makeActionCreator(LESSON_PAGES_REFRESH, 'pages')

actions.search = (searchStr, internalNotes = false) => (dispatch) => {
  if (internalNotes) {
    dispatch(listActions.resetFilters(selectors.LIST_NAME, [{property: 'contentAndNote', value: searchStr}]))
  } else {
    dispatch(listActions.resetFilters(selectors.LIST_NAME, [{property: 'content', value: searchStr}]))
  }

  dispatch(listActions.invalidateData(selectors.LIST_NAME))
}

actions.loadChapter = (chapter) => dispatch => {
  dispatch(actions.setCurrentPage(chapter.slug))

  if (!chapter.previousSlug) {
    // first chapter
    dispatch(resourceActions.triggerLifecycleAction('play'))
  }

  if (!chapter.nextSlug) {
    // last chapter
    dispatch(resourceActions.triggerLifecycleAction('end'))
  }
}

actions.editChapter = (lessonId, chapter) => dispatch => {
  dispatch(formActions.resetForm(selectors.CHAPTER_EDIT_FORM_NAME, chapter, false))
  dispatch(actions.setCurrentPage(chapter.slug))
}

actions.createChapter = (lessonId, parentChapterSlug) => formActions.resetForm(selectors.CHAPTER_EDIT_FORM_NAME, {parentSlug: parentChapterSlug}, true)

actions.deleteChapter = (lessonId, chapterSlug) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_lesson_chapter_delete', {lessonId: lessonId, slug: chapterSlug}],
    request: {
      method: 'DELETE'
    },
    success: (response) => dispatch(actions.refreshPages(response))
  }
})

actions.moveChapter = (lessonId, chapterSlug, positionData) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_lesson_chapter_move', {lessonId: lessonId, slug: chapterSlug}],
    request: {
      method: 'PUT',
      body: JSON.stringify(positionData)
    },
    success: (response) => dispatch(actions.refreshPages(response))
  }
})

actions.downloadChapterPdf = (lessonId, chapterId) => ({
  [API_REQUEST]: {
    url: ['icap_lesson_chapter_export_pdf', {lessonId: lessonId, chapter: chapterId}],
    request: {
      method: 'GET'
    }
  }
})
