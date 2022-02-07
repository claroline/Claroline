import {connect} from 'react-redux'

import {ContentTab as ContentTabComponent} from '#/plugin/analytics/dashboard/administration/content/components/tab'
import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const ContentTab = connect(
  (state) => ({
    count: selectors.count(state)
  })
)(ContentTabComponent)

export {
  ContentTab
}
