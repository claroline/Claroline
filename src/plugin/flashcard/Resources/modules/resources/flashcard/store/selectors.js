import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'flashcard'
const FLASHCARD_UPDATE_PROGRESSION = 'FLASHCARD_UPDATE_PROGRESSION'

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

const empty = createSelector(
  [cards],
  (cards) => 0 === cards.length
)

const display = createSelector(
  [flashcardDeck],
  (flashcardDeck) => flashcardDeck.display || {}
)

const overviewMessage = createSelector(
  [display],
  (display) => display.description
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
  FLASHCARD_UPDATE_PROGRESSION,

  resource,
  flashcardDeck,
  flashcardDeckProgression,
  id,
  cards,
  empty,
  overviewMessage,
  showOverview,
  showEndPage
}
