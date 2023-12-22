import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeTool as BadgeToolComponent} from '#/plugin/open-badge/tools/badges/components/tool'
import {actions, selectors} from '#/plugin/open-badge/tools/badges/store'

const BadgeTool = connect(
  (state) => ({
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    contextData: toolSelectors.contextData(state)
  }),
  dispatch => ({
    openBadge(id = null, workspace = null) {
      dispatch(actions.openBadge(selectors.FORM_NAME, id, workspace))
    },
    openAssertion(id) {
      dispatch(actions.openAssertion(selectors.STORE_NAME +'.assertion', id))
    }
  })
)(BadgeToolComponent)

export {
  BadgeTool
}
