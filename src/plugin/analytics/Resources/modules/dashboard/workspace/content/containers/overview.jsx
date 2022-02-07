import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ContentOverview as ContentOverviewComponent} from '#/plugin/analytics/dashboard/workspace/content/components/overview'

const ContentOverview = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state)
  })
)(ContentOverviewComponent)

export {
  ContentOverview
}
