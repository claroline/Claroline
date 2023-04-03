import {connect} from 'react-redux'

import {constants} from '#/plugin/cursus/constants'
import {actions} from '#/plugin/cursus/event/store'
import {EventParticipants as EventParticipantsComponent} from '#/plugin/cursus/event/components/participants'

const EventParticipants = connect(
  null,
  (dispatch) => ({
    addUsers(eventId, users, type = constants.LEARNER_TYPE) {
      dispatch(actions.addUsers(eventId, users, type))
    },
    inviteUsers(eventId, users) {
      dispatch(actions.inviteUsers(eventId, users))
    },
    inviteGroups(eventId, groups) {
      dispatch(actions.inviteGroups(eventId, groups))
    },
    addGroups(eventId, groups, type = constants.LEARNER_TYPE) {
      dispatch(actions.addGroups(eventId, groups, type))
    },
    setPresenceStatus(eventId, presences, status) {
      dispatch(actions.setPresenceStatus(eventId, presences, status))
    }
  })
)(EventParticipantsComponent)

export {
  EventParticipants
}
