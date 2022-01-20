import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/path/resources/path/store'
import {Progression as ProgressionComponent} from '#/plugin/path/analytics/resource/progression/components/progression'

const Progression = connect(
  (state) => ({
    nodeId: resourceSelectors.id(state),
    path: selectors.path(state)
  })
)(ProgressionComponent)

export {
  Progression
}
