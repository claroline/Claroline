import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security/permissions'

import {FlashcardResource as FlashcardResourceComponent} from '#/plugin/flashcard/resources/flashcard/components/resource'
import {actions, selectors} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardResource = withRouter(
  connect(
    (state) => ({
      flashcardDeck: selectors.flashcardDeck(state),
      overview: selectors.showOverview(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    }),
    (dispatch) => ({
      async getAttempt(deckId) {
        return dispatch(actions.getAttempt(deckId))
      }
    })
  )(FlashcardResourceComponent)
)

export {
  FlashcardResource
}
