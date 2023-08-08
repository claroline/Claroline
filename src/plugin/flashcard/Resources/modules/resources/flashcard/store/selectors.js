import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'flashcard_deck'

const resource = (state) => state[STORE_NAME]

const flashcardDeck = createSelector(
  [resource],
  (resource) => resource.flashcardDeck
)

const showOverview = createSelector(
  [flashcardDeck],
  (flashcardDeck) => get(flashcardDeck, 'display.showOverview') || false
)

export const selectors = {
  STORE_NAME,
  flashcardDeck,
  showOverview
}
