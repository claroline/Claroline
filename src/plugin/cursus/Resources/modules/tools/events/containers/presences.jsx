import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventsPresences as EventsPresencesComponent} from '#/plugin/cursus/tools/events/components/presences'

const EventsPresences = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextId: toolSelectors.contextId(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canRegister: hasPermission('register', toolSelectors.toolData(state))
  })
)(EventsPresencesComponent)

export {
  EventsPresences
}
