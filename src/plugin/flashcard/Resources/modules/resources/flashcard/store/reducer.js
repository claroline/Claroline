import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {ATTEMPT_LOAD} from '#/plugin/flashcard/resources/flashcard/store/actions'

import {selectors as editorSelectors, reducer as editorReducer} from '#/plugin/flashcard/resources/flashcard/editor/store'

const reducer = combineReducers(Object.assign({
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'flashcard')]: (state, action) => action.resourceData || state,
    [ATTEMPT_LOAD] : (state, action) => {
      const newState = cloneDeep(state)
      newState.attempt = action.data.attempt
      return newState
    },
    [`${FORM_SUBMIT_SUCCESS}/${editorSelectors.FORM_NAME}`]: (state, action) => ({
      attempt : state.attempt,
      flashcardDeck: action.updatedData
    })
  })
}, editorReducer))

export {
  reducer
}
