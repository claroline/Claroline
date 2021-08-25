import {connect} from 'react-redux'

import {selectors as listSelectors} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/analytics/administration/dashboard/store'
import {DashboardWorkspaces as DashboardWorkspacesComponent} from '#/plugin/analytics/administration/dashboard/components/workspaces'

const DashboardWorkspaces = connect(
  (state) => ({
    searchQueryString: listSelectors.queryString(listSelectors.list(state, selectors.STORE_NAME + '.evaluations'))
  })
)(DashboardWorkspacesComponent)

export {
  DashboardWorkspaces
}
