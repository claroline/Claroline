import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {DataListProperty as DataListPropertyTypes} from '#/main/core/data/list/prop-types'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'

const ListWidgetComponent = props =>
  <DataListContainer
    name="list"
    title={props.title}
    level={3}
    fetch={{
      url: props.fetchUrl,
      autoload: true
    }}
    open={{
      action: (row) => props.openRow(row, props.open)
    }}
    definition={props.definition}
    card={props.card}
    display={{
      current: props.display,
      available: props.availableDisplays
    }}
  />

ListWidgetComponent.propTypes = {
  title: T.string,
  open: T.func,
  openRow: T.func.isRequired,
  fetchUrl: T.oneOfType([T.string, T.array]).isRequired,

  /**
   * Definition of the data properties.
   */
  definition: T.arrayOf(
    T.shape(DataListPropertyTypes.propTypes)
  ).isRequired,
  card: T.func,
  display: T.string,
  availableDisplays: T.array
}

const ListWidget = connect(
  (state) => ({
    fetchUrl: state.config.fetchUrl,
    open: state.config.open,
    definition: state.config.definition,
    card: state.config.card,
    display: state.config.display,
    availableDisplays: state.config.availableDisplays,
    title: state.config.title
  }),
  (dispatch, ownProps) => ({
    openRow(row, callback) {
      // this is slightly ugly to pass the dispatcher like this
      return callback(row, dispatch)
    }
  })
)(ListWidgetComponent)

export {
  ListWidget
}
