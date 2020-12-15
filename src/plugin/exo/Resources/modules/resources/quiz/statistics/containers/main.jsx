import {connect} from 'react-redux'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'

import {StatisticsMain as StatisticsMainComponent} from '#/plugin/exo/resources/quiz/statistics/components/main'
import {selectors} from '#/plugin/exo/resources/quiz/statistics/store'

const StatisticsMain = connect(
  (state) => ({
    quiz: quizSelectors.quiz(state),
    showTitles: quizSelectors.showTitles(state),
    numbering: quizSelectors.numbering(state),
    stats: selectors.statistics(state)
  })
)(StatisticsMainComponent)

export {
  StatisticsMain
}
