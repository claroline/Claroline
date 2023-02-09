import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {PathOverview as PathOverviewComponent} from '#/plugin/path/resources/path/components/overview'
import {selectors} from '#/plugin/path/resources/path/store'

const PathOverview = connect(
  (state) => ({
    basePath: resourceSelectors.path(state),
    resourceId: resourceSelectors.id(state),
    path: selectors.path(state),
    empty: selectors.empty(state),
    resourceNode: resourceSelectors.resourceNode(state),
    evaluation: resourceSelectors.resourceEvaluation(state),
    resourceEvaluations: selectors.resourceEvaluations(state),
    stepsProgression: selectors.stepsProgression(state)
  })
)(PathOverviewComponent)

export {
  PathOverview
}
