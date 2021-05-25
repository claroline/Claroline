import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {LOAD_ATTEMPTS_STATS} from '#/plugin/exo/resources/quiz/statistics/store/actions'

export const reducer = combineReducers({
  loaded: makeReducer(false, {
    [LOAD_ATTEMPTS_STATS]: () => true
  }),
  data: makeReducer({}, {
    [LOAD_ATTEMPTS_STATS]: (state, action) => action.stats
  })
})
