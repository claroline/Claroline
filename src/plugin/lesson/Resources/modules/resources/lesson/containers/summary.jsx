import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonSummary as LessonSummaryComponent} from '#/plugin/lesson/resources/lesson/components/summary'
import {MODAL_LESSON_CHAPTER_DELETE} from '#/plugin/lesson/resources/lesson/modals/chapter'

const LessonSummary = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      lesson: selectors.lesson(state),
      tree: selectors.treeData(state),
      overview: selectors.showOverview(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
      internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)),
      canExport: selectors.canExport(state)
    }),
    (dispatch) => ({
      downloadChapterPdf(lessonId, chapterId) {
        return dispatch(actions.downloadChapterPdf(lessonId, chapterId))
      },
      delete: (lessonId, chapterSlug, chapterTitle, history, path) => {
        dispatch(modalActions.showModal(MODAL_LESSON_CHAPTER_DELETE, {
          chapterTitle: chapterTitle,
          deleteChapter: (deleteChildren) => dispatch(actions.deleteChapter(lessonId, chapterSlug, deleteChildren)).then((success) => {
            history.push(success.slug ? `${path}/${success.slug}` : path)
          })
        }))
      },
      search(searchStr, internalNotes = false) {
        dispatch(actions.search(searchStr, internalNotes))
      }
    })
  )(LessonSummaryComponent)
)

export {
  LessonSummary
}
