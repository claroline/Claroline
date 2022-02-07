import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ActivityTab as ActivityTabComponent} from '#/plugin/analytics/dashboard/workspace/activity/components/tab'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const ActivityTab = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state),
    count: selectors.count(state)
  })
)(ActivityTabComponent)

export {
  ActivityTab
}
