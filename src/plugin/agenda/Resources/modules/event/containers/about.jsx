import {connect} from 'react-redux'

import {EventAbout as EventAboutComponent} from '#/plugin/agenda/event/components/about'
import {actions} from '#/plugin/agenda/event/store'

const EventAbout = connect(
  null,
  (dispatch) => ({
    delete(event) {
      return dispatch(actions.delete(event))
    }
  })
)(EventAboutComponent)

export {
  EventAbout
}
