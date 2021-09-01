import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventsMenu as EventsMenuComponent} from '#/plugin/cursus/tools/events/components/menu'

const EventsMenu = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canRegister: hasPermission('register', toolSelectors.toolData(state))
  })
)(EventsMenuComponent)

export {
  EventsMenu
}
