import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {getSource} from '#/main/app/data/sources'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

class ListWidgetParameters extends Component {
  constructor(props) {
    super(props)

    this.state = {
      source: undefined
    }
  }

  componentDidMount() {
    this.loadSourceDefinition(this.props.instance.source)
  }

  componentDidUpdate(prevProps) {
    if (this.props.instance.source !== prevProps.instance.source) {
      this.loadSourceDefinition(this.props.instance.source)
    }
  }

  loadSourceDefinition(source) {
    getSource(source, this.props.currentContext.type, this.props.currentContext.data, {}, this.props.currentUser).then(sourceDefinition => this.setState({
      source: sourceDefinition
    }))
  }

  render() {
    if (!this.state.source) {
      return null
    }

    return (
      <ListForm
        level={5}
        flush={true}
        name={this.props.name}
        dataPart="parameters"
        list={this.state.source}
        parameters={this.props.instance.parameters}
      />
    )
  }
}

ListWidgetParameters.propTypes = {
  name: T.string.isRequired,
  currentUser: T.object,
  currentContext: T.shape({
    type: T.string,
    data: T.object
  }).isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired
}

export {
  ListWidgetParameters
}
