import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {DashboardMenu as DashboardMenuComponent} from '#/plugin/analytics/tools/dashboard/components/menu'

const DashboardMenu = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state)
  })
)(DashboardMenuComponent)

export {
  DashboardMenu
}
