import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

const FORM_NAME = `${baseSelectors.STORE_NAME}.flashcardForm`
const flashcardDeck = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))
const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)
const selectors = {
  FORM_NAME,
  cards
}
const reducer = {
  flashcardForm: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, baseSelectors.STORE_NAME)]: (state, action) => action.resourceData.flashcardDeck || state
    }),
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, baseSelectors.STORE_NAME)]: (state, action) => action.resourceData.flashcardDeck|| state
    })
  })
}

export {
  reducer,
  selectors
}
