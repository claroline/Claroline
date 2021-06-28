import {connect} from 'react-redux'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'
import {AttemptsStats as AttemptsStatsComponent} from '#/plugin/exo/resources/quiz/statistics/components/attempts'

const AttemptsStats = connect(
  (state) => ({
    quizId: quizSelectors.id(state),
    steps: quizSelectors.steps(state),
    questionNumberingType: quizSelectors.questionNumbering(state)
  })
)(AttemptsStatsComponent)

export {
  AttemptsStats
}
