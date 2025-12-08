import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ListSource} from '#/main/app/content/list/containers/source'
import {getSource} from '#/main/app/data/sources'

import {selectors} from '#/main/core/widget/types/list/store'
import {ListWidgetParameters as ListWidgetParametersTypes} from '#/main/core/widget/types/list/prop-types'

class ListWidget extends Component {
  constructor(props) {
    super(props)

    this.state = {
      source: null
    }
  }

  componentDidMount() {
    const refresher = {
      add:    this.props.invalidate,
      update: this.props.invalidate,
      delete: this.props.invalidate
    }

    getSource(this.props.source, this.props.currentContext.type, this.props.currentContext.data, refresher, this.props.currentUser)
      .then(sourceDefinition => this.setState({
        source: sourceDefinition
      }))
  }

  render() {
    if (!this.state.source) {
      return null
    }

    return (
      <ListSource
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_data_source', {
            type: this.props.source,
            context: this.props.currentContext.type,
            contextId: 'workspace' === this.props.currentContext.type ? this.props.currentContext.data.id : null,
            all: get(this.props.parameters, 'all', false) && 'all'
          }],
          autoload: true
        }}
        source={this.state.source}
        parameters={this.props.parameters}
        absolute={true}
      />
    )
  }
}

ListWidget.propTypes = {
  source: T.string,
  currentUser: T.object,
  currentContext: T.object.isRequired,
  parameters: T.shape(
    ListWidgetParametersTypes.propTypes
  ),
  invalidate: T.func.isRequired
}

export {
  ListWidget
}
