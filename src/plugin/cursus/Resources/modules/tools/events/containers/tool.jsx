import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions as eventActions, reducer as eventReducer, selectors as eventSelectors} from '#/plugin/cursus/event/store'
import {EventsTool as EventsToolComponent} from '#/plugin/cursus/tools/events/components/tool'
import {selectors} from '#/plugin/cursus/tools/events/store'

const EventsTool = withReducer(eventSelectors.STORE_NAME, eventReducer)( // not the best place to do it
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canRegister: hasPermission('register', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      open(id) {
        dispatch(eventActions.open(id))
      },
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.LIST_NAME))
      }
    })
  )(EventsToolComponent)
)

export {
  EventsTool
}
