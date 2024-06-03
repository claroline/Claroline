import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EventsMenu as EventsMenuComponent} from '#/plugin/cursus/tools/events/components/menu'
import {selectors as eventsSelectors, reducer as eventsReducer } from '#/plugin/cursus/tools/events/store'

const EventsMenu = withReducer(eventsSelectors.STORE_NAME, eventsReducer)(
  connect(
    (state) => ({
      course: eventsSelectors.course(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canRegister: hasPermission('register', toolSelectors.toolData(state))
    })
  )(EventsMenuComponent)
)

export {
  EventsMenu
}
