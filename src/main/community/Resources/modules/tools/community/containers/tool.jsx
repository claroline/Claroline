import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CommunityTool as CommunityToolComponent} from '#/main/community/tools/community/components/tool'

const CommunityTool = withRouter(
  connect(
    (state) => ({
      contextType: toolSelectors.contextType(state),
      contextData: toolSelectors.contextData(state),
      workspace: toolSelectors.contextData(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state)),
      canShowActivity: hasPermission('show_activity', toolSelectors.toolData(state))
    })
  )(CommunityToolComponent)
)

export {
  CommunityTool
}
