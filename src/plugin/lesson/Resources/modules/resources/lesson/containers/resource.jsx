import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {LessonResource as LessonResourceComponent} from '#/plugin/lesson/resources/lesson/components/resource'
import {actions, reducer, selectors} from '#/plugin/lesson/resources/lesson/store'

const LessonResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      //resourceId: resourceSelectors.id(state),
      lesson: selectors.lesson(state),
      //tree: selectors.treeData(state),
      //invalidated: selectors.treeInvalidated(state),
      //root: selectors.root(state),
      //overview: selectors.showOverview(state),
      //canExport: selectors.canExport(state),
      //canEdit: selectors.canEdit(state)
    }),
    dispatch => ({
      loadChapter(lessonId, chapterSlug) {
        dispatch(actions.loadChapter(lessonId, chapterSlug))
      },
      downloadLessonPdf(lessonId) {
        return dispatch(actions.downloadLessonPdf(lessonId))
      }
    })
  )(LessonResourceComponent)
)

export {
  LessonResource
}
