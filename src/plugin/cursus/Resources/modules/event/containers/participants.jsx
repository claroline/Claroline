import {connect} from 'react-redux'

import {constants} from '#/plugin/cursus/constants'
import {actions} from '#/plugin/cursus/event/store'
import {EventParticipants as EventParticipantsComponent} from '#/plugin/cursus/event/components/participants'

const EventParticipants = connect(
  null,
  (dispatch) => ({
    addUsers(sessionId, users, type = constants.LEARNER_TYPE) {
      dispatch(actions.addUsers(sessionId, users, type))
    },
    inviteUsers(sessionId, users) {
      dispatch(actions.inviteUsers(sessionId, users))
    },
    inviteGroups(sessionId, groups) {
      dispatch(actions.inviteGroups(sessionId, groups))
    },
    addGroups(sessionId, groups, type = constants.LEARNER_TYPE) {
      dispatch(actions.addGroups(sessionId, groups, type))
    },
    setPresenceStatus(eventId, presences, status) {
      dispatch(actions.setPresenceStatus(eventId, presences, status))
    }
  })
)(EventParticipantsComponent)

export {
  EventParticipants
}
