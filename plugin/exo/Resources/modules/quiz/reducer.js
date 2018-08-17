import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {reducers as editorReducers} from '#/plugin/exo/quiz/editor/reducers'
import {reducers as playerReducers} from '#/plugin/exo/quiz/player/reducers'
import {reducer as papersReducer} from '#/plugin/exo/quiz/papers/reducer'
import {reduceCorrection} from '#/plugin/exo/quiz/correction/reducer'

export const reducer = combineReducers({
  noServer: makeReducer(false, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.noServer || false
  }),
  quiz: editorReducers.quiz,
  steps: editorReducers.steps,
  items: editorReducers.items,
  editor: editorReducers.editor,

  // TODO : combine in a sub object for cleaner store
  testMode: playerReducers.testMode,
  currentStep: playerReducers.currentStep,
  paper: playerReducers.paper,
  answers: playerReducers.answers,

  papers: papersReducer,

  correction: reduceCorrection
})
