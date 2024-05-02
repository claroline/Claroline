import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as toolSelectors}  from '#/main/core/tool/store'
import {actions, selectors, reducer} from '#/plugin/cursus/course/store'
import {Course as CourseComponent} from '#/plugin/cursus/course/components/main'

const Course = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      course: selectors.course(state),
      contextType: toolSelectors.contextType(state),
      defaultSession: selectors.defaultSession(state),
      activeSession: selectors.activeSession(state),
      registrations: selectors.sessionRegistrations(state),
      availableSessions: selectors.availableSessions(state),
      participantsView: selectors.participantsView(state)
    }),
    (dispatch) => ({
      reload(courseSlug) {
        return dispatch(actions.open(courseSlug, true))
      },
      register(course, sessionId = null, registrationData = null) {
        return dispatch(actions.register(course, sessionId, registrationData))
      },
      openSession(sessionId) {
        dispatch(actions.openSession(sessionId))
      },
      switchParticipantsView(viewMode) {
        dispatch(actions.switchParticipantsView(viewMode))
      }
    })
  )(CourseComponent))

export {
  Course
}
