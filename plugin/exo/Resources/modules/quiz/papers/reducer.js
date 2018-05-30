import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {update} from '#/plugin/exo/utils/utils'
import {PAPERS_INIT, PAPER_CURRENT, PAPER_ADD} from '#/plugin/exo/quiz/papers/actions'
import {utils} from '#/plugin/exo/quiz/papers/utils'

const reducer = combineReducers({
  list: makeListReducer('papers.list', {}, {
    invalidated: makeReducer(false, {
      [PAPER_ADD]: () => true
    })
  }),
  papers: makeReducer({}, {
    [PAPERS_INIT]: (state, action) => action.papers,
    [PAPER_ADD]: (state, action) => {
      const newState = cloneDeep(state)
      const paper = !action.paper.score ?
        update(action.paper, {score: {$set: utils.computeScore(action.paper, action.paper.answers)}}):
        action.paper

      update(newState, {[paper.id]:{$set: paper}})

      return newState
    }
  }),
  current: makeReducer(null, {
    [PAPER_CURRENT]: (state, action) => action.paper
  })
})

export {
  reducer
}
