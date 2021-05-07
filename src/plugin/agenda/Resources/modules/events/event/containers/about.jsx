import {connect} from 'react-redux'

import {EventAbout as EventAboutComponent} from '#/plugin/agenda/events/event/components/about'
import {actions, selectors} from '#/plugin/agenda/events/event/store'

const EventAbout = connect(
  (state) => ({
    agendaEvent: selectors.event(state)
  }),
  (dispatch) => ({
    open(eventId) {
      dispatch(actions.open(eventId))
    },
    sendInvitations(eventId) {
      dispatch(actions.sendInvitations(eventId))
    }
  })
)(EventAboutComponent)

export {
  EventAbout
}
