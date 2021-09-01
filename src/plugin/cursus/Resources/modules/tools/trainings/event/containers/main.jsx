import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {actions as eventActions, reducer as eventReducer, selectors as eventSelectors} from '#/plugin/cursus/event/store'
import {EventMain as EventMainComponent} from '#/plugin/cursus/tools/trainings/event/components/main'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'

const EventMain = withReducer(eventSelectors.STORE_NAME, eventReducer)( // not the best place to do it
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentContext: toolSelectors.context(state),
      authenticated: securitySelectors.isAuthenticated(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canRegister: hasPermission('register', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      open(id) {
        dispatch(eventActions.open(id))
      },
      invalidateList() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(EventMainComponent)
)

export {
  EventMain
}
