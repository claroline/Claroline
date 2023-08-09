import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security/permissions'

import {FlashcardDeckResource as FlashcardDeckResourceComponent} from '#/plugin/flashcard/resources/flashcard/components/resource'
import {selectors} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardDeckResource = withRouter(
  connect(
    (state) => ({
      flashcardDeck: selectors.flashcardDeck(state),
      overview: selectors.showOverview(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(FlashcardDeckResourceComponent)
)

export {
  FlashcardDeckResource
}
