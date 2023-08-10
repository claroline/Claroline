import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {FlashcardDeckMenu as FlashcardDeckMenuComponent} from '#/plugin/flashcard/resources/flashcard/components/menu'

const FlashcardDeckMenu = withRouter(
  connect(
    (state) => ({
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(FlashcardDeckMenuComponent)
)

export {
  FlashcardDeckMenu
}
