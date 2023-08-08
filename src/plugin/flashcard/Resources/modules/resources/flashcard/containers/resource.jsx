import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security/permissions'

import {FlashcardDeckResource as FlashcardDeckResourceComponent} from '#/plugin/flashcard/resources/flashcard/components/resource'
import {reducer, selectors} from '#/plugin/flashcard/resources/flashcard/store'

const FlashcardDeckResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        showOverview: selectors.showOverview(state),
        editable: hasPermission('edit', resourceSelectors.resourceNode(state))
      })
    )(FlashcardDeckResourceComponent)
  )
)

export {
  FlashcardDeckResource
}
