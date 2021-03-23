import {connect} from 'react-redux'

import {EventDetails as EventDetailsComponent} from '#/plugin/cursus/events/event/components/details'
import {selectors, actions} from '#/plugin/cursus/event/store'

const EventDetails = connect(
  (state) => ({
    trainingEvent: selectors.event(state)
  }),
  (dispatch) => ({
    open(eventId, force) {
      return dispatch(actions.open(eventId, force))
    }
  })
)(EventDetailsComponent)

export {
  EventDetails
}
