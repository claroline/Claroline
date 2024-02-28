import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventsPresences as EventsPresencesComponent} from '#/plugin/cursus/tools/events/components/presences'

const EventsPresences = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextId: toolSelectors.contextId(state)
  })
)(EventsPresencesComponent)

export {
  EventsPresences
}
