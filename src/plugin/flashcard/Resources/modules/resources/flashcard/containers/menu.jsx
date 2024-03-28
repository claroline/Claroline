import {connect} from 'react-redux'

import {FlashcardMenu as FlashcardMenuComponent} from '#/plugin/flashcard/resources/flashcard/components/menu'
import {selectors} from '#/plugin/path/resources/path/store'

const FlashcardMenu = connect(
  (state) => ({
    overview: selectors.showOverview(state)
  })
)(FlashcardMenuComponent)

export {
  FlashcardMenu
}
