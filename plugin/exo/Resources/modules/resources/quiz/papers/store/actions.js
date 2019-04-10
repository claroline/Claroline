import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {utils as paperUtils} from '#/plugin/exo/quiz/papers/utils'

export const PAPER_ADD = 'PAPER_ADD'
export const PAPER_DISPLAY = 'PAPER_DISPLAY'
export const PAPER_CURRENT = 'PAPER_DISPLAY'

export const actions = {}

actions.setCurrentPaper = makeActionCreator(PAPER_CURRENT, 'paper')
actions.addPaper = makeActionCreator(PAPER_ADD, 'paper')

actions.loadCurrentPaper = (quizId, paperId) => ({
  [API_REQUEST]: {
    url: ['exercise_paper_get', {exerciseId: quizId, id: paperId}],
    success: (data, dispatch) => {
      if (data.structure.parameters.showScoreAt !== 'never' && !data.score && data.score !== 0) {
        data['score'] = paperUtils.computeScore(data, data.answers)
      }
      dispatch(actions.setCurrentPaper(data))
    }
  }
})
