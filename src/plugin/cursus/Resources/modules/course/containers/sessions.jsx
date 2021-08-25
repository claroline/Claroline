import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseSessions as CourseSessionsComponent} from '#/plugin/cursus/course/components/sessions'

const CourseSessions = withRouter(
  connect(
    (state) => ({
      registrations: selectors.sessionRegistrations(state)
    }),
    (dispatch) => ({
      reload(courseSlug) {
        return dispatch(actions.open(courseSlug, true))
      },
      register(course, sessionId) {
        return dispatch(actions.register(course, sessionId))
      }
    })
  )(CourseSessionsComponent)
)

export {
  CourseSessions
}
