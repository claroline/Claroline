import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {QUIZ_LOAD_ATTEMPT, QUIZ_LOAD_ATTEMPT_STATS} from '#/plugin/exo/evaluation/store/actions'

const reducer = combineReducers({
  paper: makeReducer(null, {
    [QUIZ_LOAD_ATTEMPT]: (state, action) => action.paper
  }),
  stats: makeReducer({}, {
    [QUIZ_LOAD_ATTEMPT_STATS]: (state, action) => action.stats
  })
})

export {
  reducer
}
