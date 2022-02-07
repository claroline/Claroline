import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ContentTab as ContentTabComponent} from '#/plugin/analytics/dashboard/workspace/content/components/tab'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const ContentTab = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state),
    count: selectors.count(state)
  })
)(ContentTabComponent)

export {
  ContentTab
}
