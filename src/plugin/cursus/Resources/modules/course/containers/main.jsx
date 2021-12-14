import {connect} from 'react-redux'

import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseMain as CourseMainComponent} from '#/plugin/cursus/course/components/main'

const CourseMain = connect(
  (state) => ({
    defaultSession: selectors.defaultSession(state),
    activeSession: selectors.activeSession(state),
    activeSessionRegistration: selectors.activeSessionRegistration(state),
    courseRegistration: selectors.courseRegistration(state),
    availableSessions: selectors.availableSessions(state)
  }),
  (dispatch) => ({
    openSession(sessionId) {
      dispatch(actions.openSession(sessionId))
    },
    openForm(slug, defaultProps) {
      dispatch(actions.openForm(slug, defaultProps))
    }
  })
)(CourseMainComponent)

export {
  CourseMain
}
