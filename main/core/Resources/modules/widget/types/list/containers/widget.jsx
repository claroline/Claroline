import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ListWidget as ListWidgetComponent} from '#/main/core/widget/types/list/components/widget'
import {reducer, selectors} from '#/main/core/widget/types/list/store'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const ListWidget = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      source: contentSelectors.source(state),
      parameters: contentSelectors.parameters(state),
      context: contentSelectors.context(state)
    })
  )(ListWidgetComponent)
)

export {
  ListWidget
}
