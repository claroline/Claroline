import {connect} from 'react-redux'

import {constants} from '#/plugin/cursus/constants'
import {actions, selectors} from '#/plugin/cursus/course/store'
import {SessionParticipants as SessionParticipantsComponent} from '#/plugin/cursus/session/components/participants'

const SessionParticipants = connect(
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
)(SessionParticipantsComponent)

export {
  SessionParticipants
}
