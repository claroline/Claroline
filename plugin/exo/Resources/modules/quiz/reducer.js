import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducers as editorReducers} from './editor/reducers'
import {reducers as playerReducers} from './player/reducers'
import {reducePapers} from './papers/reducer'
import {reduceCorrection} from './correction/reducer'

export const reducer = {
  noServer: makeReducer(false, {}),
  quiz: editorReducers.quiz,
  steps: editorReducers.steps,
  items: editorReducers.items,
  editor: editorReducers.editor,

  // TODO : combine in a sub object for cleaner store
  testMode: playerReducers.testMode,
  currentStep: playerReducers.currentStep,
  paper: playerReducers.paper,
  answers: playerReducers.answers,

  papers: reducePapers,

  correction: reduceCorrection
}
