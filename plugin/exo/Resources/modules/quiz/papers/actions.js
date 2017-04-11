import invariant from 'invariant'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {navigate} from './../router'
import {actions as baseActions} from './../actions'
import {VIEW_PAPERS, VIEW_PAPER} from './../enums'
import {selectors} from './selectors'
import {normalize} from './normalizer'
import {REQUEST_SEND} from './../../api/actions'

export const PAPER_ADD = 'PAPER_ADD'
export const PAPERS_LIST = 'PAPERS_LIST'
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
  [REQUEST_SEND]: {
    route: ['exercise_papers', {exerciseId: quizId}],
    request: {method: 'GET'},
    success: (data, dispatch) => {
      dispatch(initPapers(normalize(data)))
      dispatch(setPaperFetched())
    },
    failure: () => navigate('overview')
  }
})

actions.displayPaper = id => {
  invariant(id, 'Paper id is mandatory')
  return (dispatch, getState) => {
    if (!selectors.papersFetched(getState()) && !selectors.papers(getState())[id]) {
      dispatch(actions.fetchPapers(selectors.quizId(getState()))).then(() => {
        dispatch(actions.setCurrentPaper(id))
        dispatch(baseActions.updateViewMode(VIEW_PAPER))
      })
    } else {
      dispatch(actions.setCurrentPaper(id))
      dispatch(baseActions.updateViewMode(VIEW_PAPER))
    }
  }
}

actions.listPapers = () => {
  return (dispatch, getState) => {
    if (!selectors.papersFetched(getState())) {
      dispatch(actions.fetchPapers(selectors.quizId(getState()))).then(() => {
        dispatch(baseActions.updateViewMode(VIEW_PAPERS))
      })
    } else {
      dispatch(baseActions.updateViewMode(VIEW_PAPERS))
    }
  }
}
