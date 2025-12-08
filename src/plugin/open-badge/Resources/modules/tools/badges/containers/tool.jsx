import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeTool as BadgeToolComponent} from '#/plugin/open-badge/tools/badges/components/tool'
import {actions, reducer, selectors} from '#/plugin/open-badge/tools/badges/store'

const BadgeTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      contextType: toolSelectors.contextType(state),
      contextData: toolSelectors.contextData(state)
    }),
    dispatch => ({
      openBadge(id = null, workspace = null) {
        dispatch(actions.openBadge(id, workspace))
      },
      openAssertion(badgeId) {
        dispatch(actions.openAssertion(badgeId))
      }
    })
  )(BadgeToolComponent)

)
export {
  BadgeTool
}
