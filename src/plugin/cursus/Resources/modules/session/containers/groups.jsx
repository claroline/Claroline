import {connect} from 'react-redux'

import {actions} from '#/plugin/cursus/course/store'
import {SessionGroups as SessionGroupsComponent} from '#/plugin/cursus/session/components/groups'

const SessionGroups = connect(
  null,
  (dispatch) => ({
    inviteGroups(groups) {
      dispatch(actions.inviteGroups(groups))
    },
    moveGroups(targetId, sessionGroups, type) {
      dispatch(actions.moveGroups(targetId, sessionGroups, type))
    }
  })
)(SessionGroupsComponent)

export {
  SessionGroups
}
