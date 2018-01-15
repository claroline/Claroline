/* global process, require */

import {
  applyMiddleware,
  compose,
  createStore as baseCreate
} from 'redux'
import thunk from 'redux-thunk'

import {apiMiddleware} from '#/main/core/api/middleware'
import {makeResourceReducer} from '#/main/core/resource/reducer'
import {reducers as quizReducers} from './reducers'
import {reducers as editorReducers} from './editor/reducers'
import {reducers as playerReducers} from './player/reducers'
import {reducePapers} from './papers/reducer'
import {reduceCorrection} from './correction/reducer'

const middleware = [apiMiddleware, thunk]

if (process.env.NODE_ENV !== 'production') {
  const freeze = require('redux-freeze')
  middleware.push(freeze)
}

const identity = (state = null) => state

export function makeReducer(editable) {
  return makeResourceReducer({}, {
    noServer: identity,
    viewMode: quizReducers.viewMode,
    quiz: editable ? editorReducers.quiz : identity,
    steps: editable ? editorReducers.steps : identity,
    items: editable ? editorReducers.items : identity,
    editor: editable ? editorReducers.editor : identity,

    // TODO : combine in a sub object for cleaner store
    testMode: playerReducers.testMode,
    currentStep: playerReducers.currentStep,
    paper: playerReducers.paper,
    answers: playerReducers.answers,

    papers: reducePapers,

    correction: reduceCorrection
  })
}

export function createStore(initialState, editable = true) {
  return baseCreate(makeReducer(editable), initialState, compose(
    applyMiddleware(...middleware),
    window.devToolsExtension ? window.devToolsExtension() : f => f
  ))
}
