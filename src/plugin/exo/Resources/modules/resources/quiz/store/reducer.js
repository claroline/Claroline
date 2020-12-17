import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {reducers as playerReducers} from '#/plugin/exo/quiz/player/reducers'
import {reducer as editorReducer, selectors as editorSelectors} from '#/plugin/exo/resources/quiz/editor/store'
import {reducer as playerReducer, selectors as playerSelectors} from '#/plugin/exo/resources/quiz/player/store'
import {reducer as papersReducer, selectors as papersSelectors} from '#/plugin/exo/resources/quiz/papers/store'
import {reducer as correctionReducer, selectors as correctionSelectors} from '#/plugin/exo/resources/quiz/correction/store'
import {reducer as statisticsReducer, selectors as statisticsSelectors} from '#/plugin/exo/resources/quiz/statistics/store'
import {reducer as docimologyReducer, selectors as docimologySelectors} from '#/plugin/exo/docimology/store'

export const reducer = combineReducers({
  quiz: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.quiz || state,
    // replaces quiz data after success updates
    [`${FORM_SUBMIT_SUCCESS}/${editorSelectors.FORM_NAME}`]: (state, action) => action.updatedData
  }),
  lastAttempt: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.lastAttempt || state
  }),

  // sections
  [playerSelectors.STORE_NAME]: playerReducer,
  [editorSelectors.STORE_NAME]: editorReducer,
  [papersSelectors.STORE_NAME]: papersReducer,
  [correctionSelectors.STORE_NAME]: correctionReducer,
  [statisticsSelectors.STORE_NAME]: statisticsReducer,
  [docimologySelectors.STORE_NAME]: docimologyReducer,

  // TODO : combine in a sub object for cleaner store
  testMode: playerReducers.testMode,
  currentStep: playerReducers.currentStep,
  paper: playerReducers.paper,
  answers: playerReducers.answers
})
