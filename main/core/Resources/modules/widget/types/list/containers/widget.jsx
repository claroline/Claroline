import React, {Component} from 'react'
import {connect} from 'react-redux'
import isEqual from 'lodash/isEqual'

import {withReducer} from '#/main/app/store/components/withReducer'

import {ListWidget as ListWidgetComponent} from '#/main/core/widget/types/list/components/widget'
import {makeListWidgetReducer, selectors} from '#/main/core/widget/types/list/store'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

class Widget extends Component {
  shouldComponentUpdate(nextProps) {
    return !isEqual(nextProps, this.props)
  }

  render() {
    const ListWidgetInstance = withReducer(selectors.STORE_NAME, makeListWidgetReducer(selectors.STORE_NAME, {
      pageSize: this.props.pageSize,
      filters: this.props.filters,
      sortBy: this.props.sorting
    }))(ListWidgetComponent)

    return (
      <ListWidgetInstance {...this.props} />
    )
  }
}

const ListWidget = connect(
  (state) => ({
    source: contentSelectors.source(state),
    context: contentSelectors.context(state),

    // list configuration
    count: selectors.count(state),

    display: selectors.display(state),
    availableDisplays: selectors.availableDisplays(state),

    paginated: selectors.paginated(state),
    pageSize: selectors.pageSize(state),
    availablePageSizes: selectors.availablePageSizes(state),

    filters: selectors.filters(state),
    availableFilters: selectors.availableFilters(state),
    sorting: selectors.sorting(state),
    availableSort: selectors.availableSort(state),

    displayedColumns: selectors.displayedColumns(state),
    availableColumns: selectors.availableColumns(state)
  })
)(Widget)

export {
  ListWidget
}
