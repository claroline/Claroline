import {PAPERS_INIT, PAPER_CURRENT, PAPER_ADD, PAPER_FETCHED} from './actions'
import {update} from '../../utils/utils'

export const reducePapers = (state = {papers: [], isFetched: false}, action = {}) => {
  let index
  switch (action.type) {
    case PAPERS_INIT:
      return Object.assign({}, state, {
        papers: action.papers
      })
    case PAPER_CURRENT:
      return Object.assign({}, state, {
        current: action.id
      })
    case PAPER_ADD:
      index = state.papers.findIndex(p => p.id === action.paper.id)
      if (index === -1) {
        return Object.assign({}, state, {
          papers: update(state.papers, {$push: [action.paper]})
        })
      } else {
        return Object.assign({}, state, {
          papers: update(state.papers, {[index]:{$set: action.paper}})
        })
      }
    case PAPER_FETCHED:
      return Object.assign({}, state, {
        isFetched: true
      })
  }

  return state
}
