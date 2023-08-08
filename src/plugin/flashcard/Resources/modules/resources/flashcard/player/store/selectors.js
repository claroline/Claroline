import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

const display = createSelector(
  [baseSelectors.flashcardDeck],
  (flashcardDeck) => flashcardDeck.display || {}
)

const overviewMessage = createSelector(
  [display],
  (display) => display.description
)

const cards = createSelector(
  [baseSelectors.flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)

export const selectors = {
  overviewMessage,
  cards
}
