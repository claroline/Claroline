import {connect} from 'react-redux'

import {constants} from '#/plugin/cursus/constants'
import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseParticipants as CourseParticipantsComponent} from '#/plugin/cursus/course/components/participants'

const CourseParticipants = connect(
  (state) => ({
    stats: selectors.courseStats(state)
  }),
  (dispatch) => ({
    addUsers(sessionId, users, type = constants.LEARNER_TYPE) {
      dispatch(actions.addUsers(sessionId, users, type))
    },
    addPending(sessionId, users) {
      dispatch(actions.addPending(sessionId, users))
    },
    addGroups(sessionId, groups, type = constants.LEARNER_TYPE) {
      dispatch(actions.addGroups(sessionId, groups, type))
    },
    loadStats(courseId, sessionId) {
      dispatch(actions.fetchStats(courseId, sessionId))
    }
  })
)(CourseParticipantsComponent)

export {
  CourseParticipants
}
