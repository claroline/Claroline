import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {OverviewMain as OverviewMainComponent} from '#/plugin/exo/resources/quiz/overview/components/main'
import {selectors} from '#/plugin/exo/resources/quiz/store'

const OverviewMain = connect(
  (state) => ({
    empty: selectors.empty(state),
    editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
    quiz: selectors.quiz(state),
    userEvaluation: resourceSelectors.resourceEvaluation(state)
  })
)(OverviewMainComponent)

export {
  OverviewMain
}
