import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {ListData} from '#/main/app/content/list/containers/data'
import {getSource} from '#/main/app/data'

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
    getSource(this.props.source).then(module => this.setState({
      source: module.default
    }))
  }

  render() {
    if (!this.state.source) {
      return null
    }

    return (
      <ListData
        name={selectors.STORE_NAME}
        level={3}
        fetch={{
          url: ['apiv2_data_source', {
            type: this.props.source,
            context: this.props.context.type,
            contextId: 'workspace' === this.props.context.type ? this.props.context.data.uuid : null
          }],
          autoload: true
        }}
        primaryAction={this.state.source.parameters.primaryAction}
        definition={this.state.source.parameters.definition}
        card={this.state.source.parameters.card}
        display={{
          current: this.props.parameters.display,
          available: this.props.parameters.availableDisplays
        }}
      />
    )
  }
}

ListWidget.propTypes = {
  source: T.string,
  context: T.object.isRequired,
  parameters: T.shape(
    ListWidgetParametersTypes.propTypes
  )
}

ListWidget.defaultProps = {
  parameters: ListWidgetParametersTypes.defaultProps
}

export {
  ListWidget
}
