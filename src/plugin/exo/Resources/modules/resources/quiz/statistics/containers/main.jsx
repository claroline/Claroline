import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'

import {StatisticsMain as StatisticsMainComponent} from '#/plugin/exo/resources/quiz/statistics/components/main'
import {actions} from '#/plugin/exo/resources/quiz/statistics/store'

const StatisticsMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    quizId: quizSelectors.id(state)
  }),
  (dispatch) => ({
    statistics(quizId) {
      dispatch(actions.fetchStatistics(quizId))
    },
    docimology(quizId) {
      dispatch(actions.fetchDocimology(quizId))
    }
  })
)(StatisticsMainComponent)

export {
  StatisticsMain
}
