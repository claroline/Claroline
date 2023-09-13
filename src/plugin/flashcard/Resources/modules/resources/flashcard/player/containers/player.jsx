import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {Player as PlayerComponent} from '#/plugin/flashcard/resources/flashcard/player/components/player'
import {selectors, actions} from '#/plugin/flashcard/resources/flashcard/store'

const Player = withRouter(connect(
  state => ({
    flashcardDeck: selectors.flashcardDeck(state),
    draw: selectors.draw(state),
    flashcardDeckProgression: selectors.flashcardDeckProgression(state)
  }),
  (dispatch) => ({
    async updateProgression(cardId, isSuccessful) {
      return dispatch(actions.updateProgression(cardId, isSuccessful))
    }
  })
)(PlayerComponent))

export {
  Player
}
