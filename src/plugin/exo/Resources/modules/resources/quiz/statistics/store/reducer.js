import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  LOAD_STATISTICS,
  LOAD_DOCIMOLOGY
} from '#/plugin/exo/resources/quiz/statistics/store/actions'

const reducer = combineReducers({
  answers: makeReducer({}, {
    [LOAD_STATISTICS]: (state, action) => action.stats
  }),
  docimology: makeReducer({}, {
    [LOAD_DOCIMOLOGY]: (state, action) => action.stats
  })
})

export {
  reducer
}
