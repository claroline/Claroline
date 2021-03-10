import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {EventAbout as EventAboutComponent} from '#/plugin/agenda/events/event/components/about'
import {actions, reducer, selectors} from '#/plugin/agenda/events/event/store'

const EventAbout = withReducer(selectors.STORE_NAME, reducer)(
  connect(
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
)

export {
  EventAbout
}
