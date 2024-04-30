import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CommunityTool as CommunityToolComponent} from '#/main/community/tools/community/components/tool'
import {reducer, selectors} from '#/main/community/tools/community/store'

const CommunityTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      contextType: toolSelectors.contextType(state),
      contextData: toolSelectors.contextData(state),
      hasPendingRegistrations: selectors.hasPendingRegistrations(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canAdministrate: hasPermission('administrate', toolSelectors.toolData(state)),
      canShowActivity: hasPermission('show_activity', toolSelectors.toolData(state))
    })
  )(CommunityToolComponent)
)

export {
  CommunityTool
}
