import {connect} from 'react-redux'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'

import {AnswersStats as AnswersStatsComponent} from '#/plugin/exo/resources/quiz/statistics/components/answers'
import {selectors} from '#/plugin/exo/resources/quiz/statistics/store'

const AnswersStats = connect(
  (state) => ({
    quiz: quizSelectors.quiz(state),
    showTitles: quizSelectors.showTitles(state),
    showQuestionTitles: quizSelectors.showQuestionTitles(state),
    numbering: quizSelectors.numbering(state),
    questionNumbering: quizSelectors.questionNumbering(state),
    stats: selectors.statistics(state)
  })
)(AnswersStatsComponent)

export {
  AnswersStats
}
