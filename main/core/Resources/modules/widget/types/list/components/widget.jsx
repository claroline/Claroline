import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {ListSource} from '#/main/app/content/list/containers/source'
import {getSource} from '#/main/app/data/sources'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

import {selectors} from '#/main/core/widget/types/list/store'

// todo : implement actions

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
            contextId: 'workspace' === this.props.currentContext.type ? this.props.currentContext.data.uuid : null
          }],
          autoload: true
        }}
        source={this.state.source}
        parameters={this.props.parameters}
      />
    )
  }
}

ListWidget.propTypes = {
  source: T.string,
  currentContext: T.object.isRequired,
  parameters: T.shape(
    ListParametersTypes.propTypes
  )
}

export {
  ListWidget
}
