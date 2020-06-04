import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonMenu as LessonMenuComponent} from '#/plugin/lesson/resources/lesson/components/menu'
import {MODAL_LESSON_CHAPTER_DELETE} from '#/plugin/lesson/resources/lesson/modals/chapter'

const LessonMenu = withRouter(
  connect(
    (state) => ({
      lesson: selectors.lesson(state),
      tree: selectors.treeData(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
      canExport: selectors.canExport(state)
    }),
    (dispatch) => ({
      downloadChapterPdf(chapterId) {
        return dispatch(actions.downloadChapterPdf(chapterId))
      },
      delete: (lessonId, chapterSlug, chapterTitle, history, path) => {
        dispatch(modalActions.showModal(MODAL_LESSON_CHAPTER_DELETE, {
          chapterTitle: chapterTitle,
          deleteChapter: (deleteChildren) => dispatch(actions.deleteChapter(lessonId, chapterSlug, deleteChildren)).then((success) => {
            history.push(success.slug ? `${path}/${success.slug}` : path)
          })
        }))
      }
    })
  )(LessonMenuComponent)
)

export {
  LessonMenu
}
