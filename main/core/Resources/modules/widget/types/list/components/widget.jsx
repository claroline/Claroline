import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {ListData} from '#/main/app/content/list/containers/data'
import {getSource} from '#/main/app/data'

import {selectors} from '#/main/core/widget/types/list/store'

class ListWidget extends Component {
  constructor(props) {
    super(props)

    this.state = {
      source: null
    }
  }

  componentDidMount() {
    getSource(this.props.source).then(module => this.setState({
      source: module.default
    }))
  }

  /**
   * Creates the final list config based on the source definition
   * and the widget configuration.
   *
   * @return {Array}
   */
  computeDefinition() {
    if (this.state.source && this.state.source.parameters.definition) {
      return this.state.source.parameters.definition.map(column => Object.assign({}, column, {
        filterable: -1 !== this.props.availableFilters.indexOf(column.name),
        sortable: -1 !== this.props.availableSort.indexOf(column.name),
        displayable: -1 !== this.props.availableColumns.indexOf(column.name),
        displayed: -1 !== this.props.displayedColumns.indexOf(column.name)
      }))
    }

    return []
  }

  render() {
    if (!this.state.source) {
      return null
    }

    return (
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_data_source', {
            type: this.props.source,
            context: this.props.context.type,
            contextId: 'workspace' === this.props.context.type ? this.props.context.data.uuid : null
          }],
          autoload: true
        }}
        primaryAction={this.state.source.parameters.primaryAction}
        definition={this.computeDefinition()}
        card={this.state.source.parameters.card}
        display={{
          current: this.props.display,
          available: this.props.availableDisplays
        }}
        count={this.props.count}
        filterable={!isEmpty(this.props.availableFilters)}
        sortable={!isEmpty(this.props.availableSort)}
        selectable={false}
        paginated={this.props.paginated}
      />
    )
  }
}

ListWidget.propTypes = {
  source: T.string,
  context: T.object.isRequired,

  // list configuration
  display: T.string.isRequired,
  count: T.bool.isRequired,
  paginated: T.bool.isRequired,
  availableDisplays: T.arrayOf(T.string).isRequired,
  availableFilters: T.arrayOf(T.string).isRequired,
  availableSort: T.arrayOf(T.string).isRequired,
  displayedColumns: T.arrayOf(T.string).isRequired,
  availableColumns: T.arrayOf(T.string).isRequired
}

export {
  ListWidget
}
