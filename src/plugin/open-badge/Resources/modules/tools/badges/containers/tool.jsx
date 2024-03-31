import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeTool as BadgeToolComponent} from '#/plugin/open-badge/tools/badges/components/tool'
import {actions, reducer, selectors} from '#/plugin/open-badge/tools/badges/store'
import {withReducer} from '#/main/app/store/reducer'

const BadgeTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canGrant: hasPermission('grant', toolSelectors.toolData(state)),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state)),
      currentContext: toolSelectors.context(state)
    }),
    dispatch => ({
      openBadge(id = null, workspace = null) {
        dispatch(actions.openBadge(selectors.FORM_NAME, id, workspace))
      },
      openAssertion(id) {
        dispatch(actions.openAssertion(selectors.FORM_NAME +'.assertion', id))
      }
    })
  )(BadgeToolComponent)

)
export {
  BadgeTool
}
