import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Connections as ConnectionsComponent} from '#/plugin/analytics/analytics/resource/connections/components/connections'

const Connections = connect(
  state => ({
    resourceId: resourceSelectors.resourceNode(state).autoId
  })
)(ConnectionsComponent)

export {
  Connections
}
