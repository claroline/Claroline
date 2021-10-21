import {connect} from 'react-redux'

import {actions, selectors} from '#/plugin/cursus/event/store'
import {EventDetails as EventDetailsComponent} from '#/plugin/cursus/event/components/details'

const EventDetails = connect(
  (state) => ({
    event: selectors.event(state),
    registration: selectors.registration(state)
  }),
  (dispatch) => ({
    register(id) {
      dispatch(actions.register(id))
    }
  })
)(EventDetailsComponent)

export {
  EventDetails
}
