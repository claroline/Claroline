import {connect} from 'react-redux'

import {EventAbout as EventAboutComponent} from '#/plugin/cursus/events/event/components/about'
import {selectors} from '#/plugin/cursus/event/store'

const EventAbout = connect(
  (state) => ({
    trainingEvent: selectors.event(state)
  })
)(EventAboutComponent)

export {
  EventAbout
}
