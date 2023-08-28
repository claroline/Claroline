import get from 'lodash/get'
import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'
import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'


const STORE_NAME = 'editor'
const FORM_NAME = `${baseSelectors.STORE_NAME}.flashcardForm`

const flashcardDeck = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const flashcardDeckId = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.id
)

const flashcardDeckType = createSelector(
  [flashcardDeck],
  (flashcardDeck) => get(flashcardDeck, 'parameters.type')
)

const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)


export const selectors = {
  STORE_NAME,
  FORM_NAME,

  flashcardDeckId,
  flashcardDeckType,
  cards
}
