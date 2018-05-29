import invariant from 'invariant'

import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {selectors} from './selectors'
import quizSelectors from './../selectors'
import {normalize} from './normalizer'
import {API_REQUEST} from '#/main/app/api'

export const PAPER_ADD = 'PAPER_ADD'
export const PAPERS_INIT = 'PAPERS_INIT'
export const PAPER_DISPLAY = 'PAPER_DISPLAY'
export const PAPER_CURRENT = 'PAPER_DISPLAY'
export const PAPER_FETCHED = 'PAPER_FETCHED'

export const actions = {}

const initPapers = makeActionCreator(PAPERS_INIT, 'papers')
const setPaperFetched = makeActionCreator(PAPER_FETCHED)
actions.setCurrentPaper = makeActionCreator(PAPER_CURRENT, 'id')
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

actions.displayPaper = id => {
  invariant(id, 'Paper id is mandatory')
  return (dispatch, getState) => {
    if (!selectors.papersFetched(getState()) && (!selectors.papers(getState())[id] || quizSelectors.papersShowStatistics(getState()))) {
      dispatch(actions.fetchPapers(selectors.quizId(getState()))).then(() => {
        dispatch(actions.setCurrentPaper(id)) // TODO : remove me this should be managed by router
      })
    } else {
      dispatch(actions.setCurrentPaper(id)) // TODO : remove me this should be managed by router
    }
  }
}

actions.listPapers = () => {
  return (dispatch, getState) => {
    if (!selectors.papersFetched(getState())) {
      dispatch(actions.fetchPapers(selectors.quizId(getState())))
    }
  }
}
