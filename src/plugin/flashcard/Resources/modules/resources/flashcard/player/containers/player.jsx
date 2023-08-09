import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {FlashcardDeckPlayer as FlashcardDeckPlayerComponent} from '#/plugin/flashcard/resources/flashcard/player/components/player'
import {selectors} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardDeckPlayer = withRouter(connect(
  state => ({
    cards: selectors.cards(state)
  })
)(FlashcardDeckPlayerComponent))

export {
  FlashcardDeckPlayer
}
