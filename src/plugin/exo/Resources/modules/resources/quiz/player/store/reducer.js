import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {ATTEMPT_FINISH} from '#/plugin/exo/resources/quiz/player/store/actions'

const reducer = combineReducers({
  paperCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, quizSelectors.STORE_NAME)]: (state, action) => action.resourceData.paperCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  }),
  userPaperCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, quizSelectors.STORE_NAME)]: (state, action) => action.resourceData.userPaperCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  }),
  userPaperDayCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, quizSelectors.STORE_NAME)]: (state, action) => action.resourceData.userPaperDayCount || state,
    [ATTEMPT_FINISH]: (state) => state + 1
  }),
  // the base evaluation attempt
  attempt: makeReducer(null, {
    [ATTEMPT_FINISH]: (state, action) => action.attempt
  })
})

export {
  reducer
}
