import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {QuizOverview as QuizOverviewComponent} from '#/plugin/exo/resources/quiz/components/overview'
import {selectors} from '#/plugin/exo/resources/quiz/store'

const QuizOverview = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    empty: selectors.empty(state),
    editable: hasPermission('edit', resourceSelectors.resourceNode(state)),
    quiz: selectors.quiz(state),
    showStats: selectors.showOverviewStats(state),
    userEvaluation: resourceSelectors.resourceEvaluation(state),
    currentUserId: securitySelectors.currentUserId(state),
    resourceNode: resourceSelectors.resourceNode(state)
  })
)(QuizOverviewComponent)

export {
  QuizOverview
}
