import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CommunityTab as CommunityTabComponent} from '#/plugin/analytics/dashboard/workspace/community/components/tab'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const CommunityTab = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state),
    count: selectors.count(state)
  })
)(CommunityTabComponent)

export {
  CommunityTab
}
