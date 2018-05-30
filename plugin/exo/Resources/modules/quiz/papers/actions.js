import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/core/scaffolding/actions'

import {normalize} from '#/plugin/exo/quiz/papers/normalizer'
import {utils as paperUtils} from '#/plugin/exo/quiz/papers/utils'

export const PAPER_ADD = 'PAPER_ADD'
export const PAPERS_INIT = 'PAPERS_INIT'
export const PAPER_DISPLAY = 'PAPER_DISPLAY'
export const PAPER_CURRENT = 'PAPER_DISPLAY'
export const PAPER_FETCHED = 'PAPER_FETCHED'

export const actions = {}

const initPapers = makeActionCreator(PAPERS_INIT, 'papers')
const setPaperFetched = makeActionCreator(PAPER_FETCHED)
actions.setCurrentPaper = makeActionCreator(PAPER_CURRENT, 'paper')
actions.addPaper = makeActionCreator(PAPER_ADD, 'paper')

actions.fetchPapers = quizId => ({
  [API_REQUEST]: {
    url: ['exercise_papers', {exerciseId: quizId}],
    request: {method: 'GET'},
    success: (data, dispatch) => {
      dispatch(initPapers(normalize(data)))
      dispatch(setPaperFetched())
    }
  }
})

actions.loadCurrentPaper = (paperId) => ({
  [API_REQUEST]: {
    url: ['apiv2_exopaper_get', {id: paperId}],
    success: (data, dispatch) => {
      if (data.structure.parameters.showScoreAt !== 'never' && !data.score && data.score !== 0) {
        data['score'] = paperUtils.computeScore(data, data.answers)
      }
      dispatch(actions.setCurrentPaper(data))
    }
  }
})
