import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {reducer as editorReducer, selectors as editorSelectors} from '#/plugin/exo/resources/quiz/editor/store'
import {reducer as playerReducer, selectors as playerSelectors} from '#/plugin/exo/resources/quiz/player/store'
import {reducer as papersReducer, selectors as papersSelectors} from '#/plugin/exo/resources/quiz/papers/store'
import {reducer as correctionReducer, selectors as correctionSelectors} from '#/plugin/exo/resources/quiz/correction/store'
import {reducer as statisticsReducer, selectors as statisticsSelectors} from '#/plugin/exo/resources/quiz/statistics/store'

export const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.resource || state
  }),
  lastAttempt: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.lastAttempt || state
  }),

  // sections
  [playerSelectors.STORE_NAME]: playerReducer,
  [editorSelectors.STORE_NAME]: editorReducer,
  [papersSelectors.STORE_NAME]: papersReducer,
  [correctionSelectors.STORE_NAME]: correctionReducer,
  [statisticsSelectors.STORE_NAME]: statisticsReducer
})
