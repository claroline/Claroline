import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {EventMain as EventMainComponent} from '#/plugin/cursus/tools/trainings/event/components/main'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'

const EventMain = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    contextPath: toolSelectors.contextPath(state),
    authenticated: securitySelectors.isAuthenticated(state),
    canEdit: hasPermission('edit', toolSelectors.tool(state)),
    canRegister: hasPermission('follow', toolSelectors.tool(state))
  }),
  (dispatch) => ({
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.list'))
    }
  })
)(EventMainComponent)

export {
  EventMain
}
