import {connect} from 'react-redux'

import {ActivityTab as ActivityTabComponent} from '#/plugin/analytics/dashboard/administration/activity/components/tab'
import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const ActivityTab = connect(
  (state) => ({
    count: selectors.count(state)
  })
)(ActivityTabComponent)

export {
  ActivityTab
}
