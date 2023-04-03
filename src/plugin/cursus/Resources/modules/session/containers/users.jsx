import {connect} from 'react-redux'

import {actions, selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {SessionUsers as SessionUsersComponent} from '#/plugin/cursus/session/components/users'

const SessionUsers = connect(
  (state) => ({
    stats: selectors.courseStats(state)
  }),
  (dispatch) => ({
    updateUser(sessionUser) {
      dispatch(actions.updateUser(sessionUser))
    },
    inviteUsers(users) {
      dispatch(actions.inviteUsers(users))
    },
    moveUsers(targetId, sessionUsers, type) {
      dispatch(actions.moveUsers(targetId, sessionUsers, type))
    },
    confirmPending(users) {
      dispatch(actions.confirmPending(users))
    },
    validatePending(users) {
      dispatch(actions.validatePending(users))
    },
    movePending(courseId, sessionUsers) {
      dispatch(actions.movePending(courseId, sessionUsers))
    }
  })
)(SessionUsersComponent)

export {
  SessionUsers
}
