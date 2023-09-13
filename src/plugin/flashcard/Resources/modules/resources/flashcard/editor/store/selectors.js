import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

const FORM_NAME = `${baseSelectors.STORE_NAME}.flashcardForm`

const flashcardDeck = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)

export const selectors = {
  FORM_NAME,
  cards
}
