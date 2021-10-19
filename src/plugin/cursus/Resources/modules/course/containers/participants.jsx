import {connect} from 'react-redux'

import {constants} from '#/plugin/cursus/constants'
import {actions} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseParticipants as CourseParticipantsComponent} from '#/plugin/cursus/course/components/participants'

const CourseParticipants = connect(
  null,
  (dispatch) => ({
    addUsers(sessionId, users, type = constants.LEARNER_TYPE) {
      dispatch(actions.addUsers(sessionId, users, type))
    },
    inviteUsers(sessionId, users) {
      dispatch(actions.inviteUsers(sessionId, users))
    },
    moveUsers(sessionId, targetId, sessionUsers, type) {
      dispatch(actions.moveUsers(sessionId, targetId, sessionUsers, type))
    },
    addGroups(sessionId, groups, type = constants.LEARNER_TYPE) {
      dispatch(actions.addGroups(sessionId, groups, type))
    },
    inviteGroups(sessionId, groups) {
      dispatch(actions.inviteGroups(sessionId, groups))
    },
    moveGroups(sessionId, targetId, sessionGroups, type) {
      dispatch(actions.moveGroups(sessionId, targetId, sessionGroups, type))
    },
    addPending(sessionId, users) {
      dispatch(actions.addPending(sessionId, users))
    },
    confirmPending(sessionId, users) {
      dispatch(actions.confirmPending(sessionId, users))
    },
    validatePending(sessionId, users) {
      dispatch(actions.validatePending(sessionId, users))
    },
    movePending(courseId, sessionUsers) {
      dispatch(actions.movePending(courseId, sessionUsers))
    }
  })
)(CourseParticipantsComponent)

export {
  CourseParticipants
}
