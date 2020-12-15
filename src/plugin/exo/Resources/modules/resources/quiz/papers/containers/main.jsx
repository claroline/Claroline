import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store'
import {actions as papersActions} from '#/plugin/exo/resources/quiz/papers/store'
import {actions as statisticsActions} from '#/plugin/exo/resources/quiz/statistics/store'
import {PapersMain as PapersMainComponent} from '#/plugin/exo/resources/quiz/papers/components/main'

const PapersMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
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
