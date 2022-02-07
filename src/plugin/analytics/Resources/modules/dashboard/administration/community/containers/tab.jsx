import {connect} from 'react-redux'

import {CommunityTab as CommunityTabComponent} from '#/plugin/analytics/dashboard/administration/community/components/tab'
import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const CommunityTab = connect(
  (state) => ({
    count: selectors.count(state)
  })
)(CommunityTabComponent)

export {
  CommunityTab
}
