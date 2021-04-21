import {connect} from 'react-redux'

import {EventDetails as EventDetailsComponent} from '#/plugin/agenda/events/event/components/details'
import {selectors, actions} from '#/plugin/agenda/events/event/store'

const EventDetails = connect(
  (state) => ({
    agendaEvent: selectors.event(state)
  }),
  (dispatch) => ({
    open(eventId) {
      dispatch(actions.open(eventId))
    }
  })
)(EventDetailsComponent)

export {
  EventDetails
}
