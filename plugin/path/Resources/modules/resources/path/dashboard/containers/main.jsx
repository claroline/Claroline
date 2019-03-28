import {connect} from 'react-redux'

import {selectors as pathSelectors} from '#/plugin/path/resources/path/store'
import {DashboardMain as DashboardMainComponent} from '#/plugin/path/resources/path/dashboard/components/main'

const DashboardMain = connect(
  (state) => ({
    path: pathSelectors.path(state)
  })
)(DashboardMainComponent)

export {
  DashboardMain
}
