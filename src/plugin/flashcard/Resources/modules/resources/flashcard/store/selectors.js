import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'flashcard'

const resource = (state) => state[STORE_NAME]

const flashcardDeck = createSelector(
  [resource],
  (resource) => resource.data.flashcardDeck
)

const flashcardDeckProgression = createSelector(
  [resource],
  (resource) => resource.data.flashcardDeckProgression
)

const id = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.id
)

const cards = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.cards || []
)

const draw = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.draw
)

const empty = createSelector(
  [cards],
  (cards) => 0 === cards.length
)

const showOverview = createSelector(
  [flashcardDeck],
  (flashcardDeck) => get(flashcardDeck, 'overview.display') || false
)

const showEndPage = createSelector(
  [flashcardDeck],
  (flashcardDeck) => get(flashcardDeck, 'end.display') || false
)

export const selectors = {
  STORE_NAME,

  resource,
  flashcardDeck,
  flashcardDeckProgression,
  id,
  cards,
  draw,
  empty,
  showOverview,
  showEndPage
}
