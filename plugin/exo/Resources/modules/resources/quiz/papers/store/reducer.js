import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import quizSelect from '#/plugin/exo/quiz/selectors'
import {PAPER_CURRENT, PAPER_ADD} from '#/plugin/exo/quiz/papers/actions'

const reducer = combineReducers({
  list: makeListReducer(quizSelect.STORE_NAME+'.papers.list', {}, {
    invalidated: makeReducer(false, {
      [PAPER_ADD]: () => true
    })
  }),
  current: makeReducer(null, {
    [PAPER_CURRENT]: (state, action) => action.paper
  })
})

export {
  reducer
}
