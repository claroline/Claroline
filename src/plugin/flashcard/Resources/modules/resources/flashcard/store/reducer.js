import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {ATTEMPT_LOAD} from '#/plugin/flashcard/resources/flashcard/store/actions'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.resource || state
  }),
  attempt: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.attempt || state,
    [ATTEMPT_LOAD] : (state, action) => action.attempt
  }),
  userEvaluation: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.userEvaluation || state
  }),
  flashcardProgression: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData.flashcardProgression || state
  }),
})

export {
  reducer
}
