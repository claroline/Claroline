import {makeReducer} from '#/main/core/scaffolding/reducer'

import {USER_EVALUATION_UPDATE} from '#/main/core/resource/evaluation/actions'

const reducer = makeReducer(null, {
  [USER_EVALUATION_UPDATE]: (state, action) => action.userEvaluation
})

export {
  reducer
}
