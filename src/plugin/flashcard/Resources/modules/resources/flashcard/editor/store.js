import {createSelector} from 'reselect'

import {selectors as editorSelectors} from '#/main/core/resource/editor'

const flashcardDeck = editorSelectors.resource

const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)

const selectors = {
  flashcardDeck,
  cards
}

export {
  selectors
}
