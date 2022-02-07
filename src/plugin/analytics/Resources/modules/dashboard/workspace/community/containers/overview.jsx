import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {CommunityOverview as CommunityOverviewComponent} from '#/plugin/analytics/dashboard/workspace/community/components/overview'

const CommunityOverview = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state)
  })
)(CommunityOverviewComponent)

export {
  CommunityOverview
}
