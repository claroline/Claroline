import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'

import {selectors} from '#/plugin/cursus/tools/events/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as listActions} from '#/main/app/content/list/store'
import {EventsTool as EventsToolComponent} from '#/plugin/cursus/tools/events/components/tool'
import {actions as eventActions, reducer as eventReducer, selectors as eventSelectors} from '#/plugin/cursus/event/store'
import {actions as courseActions, reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'

const EventsTool = withReducer(courseSelectors.STORE_NAME, courseReducer)(
  withReducer(eventSelectors.STORE_NAME, eventReducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state),
        course: selectors.course(state),
        currentContext: toolSelectors.context(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state)),
        canRegister: hasPermission('register', toolSelectors.toolData(state))
      }),
      (dispatch) => ({
        open(id) {
          dispatch(eventActions.open(id))
        },
        openCourse(slug) {
          dispatch(courseActions.open(slug))
        },
        openForm(slug, defaultProps, workspace) {
          dispatch(courseActions.openForm(slug, defaultProps, workspace))
        },
        invalidateList() {
          dispatch(listActions.invalidateData(selectors.LIST_NAME))
        }
      })
    )(EventsToolComponent)
  )
)

export {
  EventsTool
}
