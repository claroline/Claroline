import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/cursus/event/store'
import {EventsDetails as EventsDetailsComponent} from '#/plugin/cursus/tools/events/components/details'

const EventsDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    event: selectors.event(state)
  })
)(EventsDetailsComponent)

export {
  EventsDetails
}
