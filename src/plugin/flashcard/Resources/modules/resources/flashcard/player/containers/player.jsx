import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {Player as PlayerComponent} from '#/plugin/flashcard/resources/flashcard/player/components/player'
import {selectors, actions} from '#/plugin/flashcard/resources/flashcard/store'

const Player = withRouter(connect(
  state => ({
    deck: selectors.flashcardDeck(state)
  }),
  (dispatch) => ({
    async updateUserProgression(cardId, isSuccessful) {
      return dispatch(actions.updateUserProgression(cardId, isSuccessful))
    }
  })
)(PlayerComponent))

export {
  Player
}
