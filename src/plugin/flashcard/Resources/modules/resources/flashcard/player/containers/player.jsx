import {connect} from 'react-redux'

import {Player as PlayerComponent} from '#/plugin/flashcard/resources/flashcard/player/components/player'
import {selectors, actions} from '#/plugin/flashcard/resources/flashcard/store'

const Player = connect(
  (state) => ({
    flashcardDeck: selectors.flashcardDeck(state),
    draw: selectors.draw(state),
    attempt: selectors.attempt(state),
    flashcardProgression: selectors.flashcardProgression(state),
    overview: selectors.showOverview(state)
  }),
  (dispatch) => ({
    async updateProgression(cardId, isSuccessful) {
      return dispatch(actions.updateProgression(cardId, isSuccessful))
    },
    async getAttempt(flashcardDeckId) {
      return dispatch(actions.getAttempt(flashcardDeckId))
    }
  })
)(PlayerComponent)

export {
  Player
}
