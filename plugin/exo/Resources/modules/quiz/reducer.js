import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

//import {reducers as editorReducers} from '#/plugin/exo/quiz/editor/reducers'
import {reducers as playerReducers} from '#/plugin/exo/quiz/player/reducers'
import {reducer as papersReducer} from '#/plugin/exo/quiz/papers/reducer'
import {reduceCorrection} from '#/plugin/exo/quiz/correction/reducer'

import {reducer as editorReducer, selectors as editorSelectors} from '#/plugin/exo/resources/quiz/editor/store'
import {reducer as playerReducer, selectors as playerSelectors} from '#/plugin/exo/resources/quiz/player/store'
import {reducer as statisticsReducer} from '#/plugin/exo/quiz/statistics/store'

export const reducer = combineReducers({
  quiz: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.quiz || state
  }),

  // sections
  [playerSelectors.STORE_NAME]: playerReducer,
  [editorSelectors.STORE_NAME]: editorReducer,

  //steps: editorReducers.steps,
  //items: editorReducers.items,

  // TODO : combine in a sub object for cleaner store
  testMode: playerReducers.testMode,
  currentStep: playerReducers.currentStep,
  paper: playerReducers.paper,
  answers: playerReducers.answers,

  papers: papersReducer,

  correction: reduceCorrection,

  statistics: statisticsReducer
})
