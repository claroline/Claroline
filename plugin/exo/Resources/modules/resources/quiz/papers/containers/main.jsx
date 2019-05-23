import {connect} from 'react-redux'

import {PapersMain as PapersMainComponent} from '#/plugin/exo/resources/quiz/papers/components/main'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'
import {actions as papersActions} from '#/plugin/exo/resources/quiz/papers/store'
import {actions as statisticsActions} from '#/plugin/exo/quiz/statistics/store'

const PapersMain = connect(
  (state) => ({
    quizId: quizSelectors.id(state),
    showStatistics: quizSelectors.showStatistics(state)
  }),
  (dispatch) => ({
    loadCurrentPaper(quizId, paperId) {
      dispatch(papersActions.loadCurrentPaper(quizId, paperId))
    },
    resetCurrentPaper() {
      dispatch(papersActions.setCurrentPaper(null))
    },
    statistics(quizId) {
      dispatch(statisticsActions.fetchStatistics(quizId))
    }
  })
)(PapersMainComponent)

export {
  PapersMain
}
