import {connect} from 'react-redux'

import {EventParticipants as EventParticipantsComponent} from '#/plugin/agenda/events/event/components/participants'
import {actions} from '#/plugin/agenda/events/event/store'

const EventParticipants = connect(
  null,
  (dispatch) => ({
    addParticipants(eventId, users) {
      dispatch(actions.addParticipants(eventId, users))
    },
    sendInvitations(eventId, users) {
      dispatch(actions.sendInvitations(eventId, users))
    }
  })
)(EventParticipantsComponent)


export {
  EventParticipants
}
