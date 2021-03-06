import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {EventMain as EventMainComponent} from '#/plugin/agenda/events/event/components/main'
import {actions, reducer, selectors} from '#/plugin/agenda/events/event/store'

const EventMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state)
    }),
    (dispatch) => ({
      open(eventId) {
        dispatch(actions.open(eventId))
      }
    })
  )(EventMainComponent)
)

export {
  EventMain
}
