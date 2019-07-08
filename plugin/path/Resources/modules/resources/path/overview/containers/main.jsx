import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {OverviewMain as OverviewMainComponent} from '#/plugin/path/resources/path/overview/components/main'
import {selectors} from '#/plugin/path/resources/path/store'

const OverviewMain = connect(
  (state) => ({
    basePath: resourceSelectors.path(state),
    path: selectors.path(state),
    empty: selectors.empty(state),
    evaluation: resourceSelectors.resourceEvaluation(state)
  })
)(OverviewMainComponent)

export {
  OverviewMain
}
