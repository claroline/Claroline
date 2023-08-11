import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {FlashcardDeckPlayer as FlashcardDeckPlayerComponent} from '#/plugin/flashcard/resources/flashcard/player/components/player'
import {selectors, actions} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardDeckPlayer = withRouter(connect(
  state => ({
    deck: selectors.flashcardDeck(state)
  }),
  (dispatch) => ({
    async updateUserProgression(cardId, isSuccessful) {
      await dispatch(actions.updateUserProgression(cardId, isSuccessful))
    }
  })
)(FlashcardDeckPlayerComponent))

export {
  FlashcardDeckPlayer
}
