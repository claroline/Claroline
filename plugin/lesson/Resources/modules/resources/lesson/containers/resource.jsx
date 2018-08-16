import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {LessonResource as LessonResourceComponent} from '#/plugin/lesson/resources/lesson/components/resource'
import {actions, reducer, selectors} from '#/plugin/lesson/resources/lesson/store'
import {constants} from '#/plugin/lesson/resources/lesson/constants'

const LessonResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      lesson: selectors.lesson(state),
      tree: selectors.treeData(state),
      invalidated: selectors.treeInvalidated(state),
      canExport: selectors.canExport(state),
      canEdit: selectors.canEdit(state)
    }),
    dispatch => ({
      loadChapter(lessonId, chapterSlug) {
        dispatch(actions.loadChapter(lessonId, chapterSlug))
      },
      editChapter(lessonId, chapterSlug) {
        dispatch(actions.editChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
      },
      copyChapter(lessonId, chapterSlug) {
        dispatch(actions.copyChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, chapterSlug))
      },
      createChapter(lessonId, parentChapterSlug = null) {
        dispatch(actions.createChapter(constants.CHAPTER_EDIT_FORM_NAME, lessonId, parentChapterSlug))
      },
      fetchChapterTree(lessonId) {
        dispatch(actions.fetchChapterTree(lessonId))
      }
    })
  )(LessonResourceComponent)
)

export {
  LessonResource
}
