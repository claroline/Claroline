import React, {Component} from 'react'
import {connect} from 'react-redux'
import isEqual from 'lodash/isEqual'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store/actions'

import {ListWidget as ListWidgetComponent} from '#/main/core/widget/types/list/components/widget'
import {makeListWidgetReducer, selectors} from '#/main/core/widget/types/list/store'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'

class Widget extends Component {
  shouldComponentUpdate(nextProps) {
    return !isEqual(nextProps, this.props)
  }

  render() {
    const ListWidgetInstance = withReducer(selectors.STORE_NAME, makeListWidgetReducer(selectors.STORE_NAME, {
      pagination: {pageSize: this.props.pageSize},
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
    currentContext: contentSelectors.context(state),
    currentUser: securitySelectors.currentUser(state),

    source: contentSelectors.source(state),
    parameters: contentSelectors.parameters(state),

    // list configuration
    pageSize: selectors.pageSize(state),
    filters: selectors.filters(state),
    sorting: selectors.sorting(state)
  }),
  (dispatch) => ({
    invalidate() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME))
    }
  })
)(Widget)

export {
  ListWidget
}
