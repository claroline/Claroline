import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ActivityTab as ActivityTabComponent} from '#/plugin/analytics//dashboard/resource/activity/components/tab'

const ActivityTab = connect(
  state => ({
    resourceId: resourceSelectors.id(state)
  })
)(ActivityTabComponent)

export {
  ActivityTab
}
