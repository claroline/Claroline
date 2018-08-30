import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ListWidget as ListWidgetComponent} from '#/main/core/widget/types/list/components/widget'
import {reducer, selectors} from '#/main/core/widget/types/list/store'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

const ListWidget = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      source: contentSelectors.source(state),
      context: contentSelectors.context(state),

      // list configuration
      display: selectors.display(state),
      availableDisplays: selectors.availableDisplays(state),
      availableFilters: selectors.availableFilters(state),
      availableSort: selectors.availableSort(state),
      displayedColumns: selectors.displayedColumns(state),
      availableColumns: selectors.availableColumns(state)
    })
  )(ListWidgetComponent)
)

export {
  ListWidget
}
