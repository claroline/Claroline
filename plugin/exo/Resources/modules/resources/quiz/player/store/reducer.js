import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {ATTEMPT_FINISH} from '#/plugin/exo/resources/quiz/player/store/actions'

const reducer = combineReducers({
  paperCount: makeReducer(0, {
    [RESOURCE_LOAD]: (state, actions) => actions.resourceData.paperCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  }),
  userPaperCount: makeReducer(0, {
    [RESOURCE_LOAD]: (state, actions) => actions.resourceData.userPaperCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  }),
  userPaperDayCount: makeReducer(0, {
    [RESOURCE_LOAD]: (state, actions) => actions.resourceData.userPaperDayCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  })
})

export {
  reducer
}
