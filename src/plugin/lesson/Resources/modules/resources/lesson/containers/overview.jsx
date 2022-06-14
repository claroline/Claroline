import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {LessonOverview as LessonOverviewComponent} from '#/plugin/lesson/resources/lesson/components/overview'
import {actions, selectors} from '#/plugin/lesson/resources/lesson/store'

const LessonOverview = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      resourceId: resourceSelectors.id(state),
      lesson: selectors.lesson(state),
      internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)),
      tree: selectors.treeData(state),
      resourceNode: resourceSelectors.resourceNode(state)
    }),
    (dispatch) => ({
      search(searchStr, internalNotes = false) {
        dispatch(actions.search(searchStr, internalNotes))
      }
    })
  )(LessonOverviewComponent)
)

export {
  LessonOverview
}
