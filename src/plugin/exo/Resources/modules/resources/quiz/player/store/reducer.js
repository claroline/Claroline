import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {ATTEMPT_FINISH} from '#/plugin/exo/resources/quiz/player/store/actions'

const reducer = combineReducers({
  // the base evaluation attempt
  attempt: makeReducer(null, {
    [ATTEMPT_FINISH]: (state, action) => action.attempt
  })
})

export {
  reducer
}
