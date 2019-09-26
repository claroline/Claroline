import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'
import {actions}    from '#/plugin/open-badge/tools/badges/store/actions'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {Tool} from '#/plugin/open-badge/tools/badges/components/tool'

const OpenBadgeTool = withRouter(
  connect(
    (state) => ({
      currentContext: toolSelectors.context(state)
    }),
    dispatch => ({
      openBadge(id = null, workspace = null) {
        dispatch(actions.openBadge(selectors.STORE_NAME +'.badges.current', id, workspace))
      },
      openAssertion(id) {
        dispatch(actions.openAssertion(selectors.STORE_NAME +'.badges.assertion', id))
      }
    })
  )(Tool)
)

export {
  OpenBadgeTool
}
