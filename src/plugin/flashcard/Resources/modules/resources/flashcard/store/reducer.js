import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'
import {selectors as editorSelectors, reducer as editorReducer} from '#/plugin/flashcard/resources/flashcard/editor/store'

const reducer = combineReducers(Object.assign({
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData || state,
    [selectors.FLASHCARD_UPDATE_PROGRESSION]: (state, action) => {
      const newState = cloneDeep(state)
      newState.flashcardDeckProgression.filter((data) => data.flashcard.id === action.id)[0].is_successful = action.is_successful
      return newState
    },
    [`${FORM_SUBMIT_SUCCESS}/${editorSelectors.FORM_NAME}`]: (state, action) => ({
      flashcardDeck: action.updatedData,
      flashcardDeckProgression: state.flashcardDeckProgression
    })
  })
}, editorReducer))

export {
  reducer
}
