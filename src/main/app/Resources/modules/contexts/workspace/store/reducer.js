import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {CONTEXT_OPEN, CONTEXT_LOAD} from '#/main/app/context/store/actions'
import {WORKSPACE_EVALUATION_UPDATE} from '#/main/app/contexts/workspace/store/actions'

const reducer = combineReducers({
  /*root: makeReducer({}, {
    [CONTEXT_OPEN]: () => ({}),
    [CONTEXT_LOAD]: (state, action) => action.contextData.root || {}
  }),*/
  userEvaluation: makeReducer(null, {
    [CONTEXT_OPEN]: () => null,
    [CONTEXT_LOAD]: (state, action) => action.contextData.userEvaluation || state,
    [WORKSPACE_EVALUATION_UPDATE]: (state, action) => action.userEvaluation
  })
})

export {
  reducer
}
