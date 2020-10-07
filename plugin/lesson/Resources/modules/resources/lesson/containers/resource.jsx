import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {LessonResource as LessonResourceComponent} from '#/plugin/lesson/resources/lesson/components/resource'
import {actions, reducer, selectors} from '#/plugin/lesson/resources/lesson/store'

const LessonResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      state => ({
        lesson: selectors.lesson(state),
        tree: selectors.treeData(state),
        invalidated: selectors.treeInvalidated(state),
        root: selectors.root(state),
        overview: selectors.showOverview(state),
        canExport: selectors.canExport(state),
        canEdit: selectors.canEdit(state)
      }),
      dispatch => ({
        loadChapter(lessonId, chapterSlug) {
          dispatch(actions.loadChapter(lessonId, chapterSlug))
        },
        editChapter(lessonId, chapterSlug) {
          dispatch(actions.editChapter(selectors.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
        },
        copyChapter(lessonId, chapterSlug) {
          dispatch(actions.copyChapter(selectors.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
        },
        createChapter(lessonId, parentChapterSlug = null) {
          dispatch(actions.createChapter(selectors.CHAPTER_EDIT_FORM_NAME, lessonId, parentChapterSlug))
        },
        fetchChapterTree(lessonId) {
          dispatch(actions.fetchChapterTree(lessonId))
        },
        downloadLessonPdf(lessonId) {
          return dispatch(actions.downloadLessonPdf(lessonId))
        }
      })
    )(LessonResourceComponent)
  )
)

export {
  LessonResource
}
