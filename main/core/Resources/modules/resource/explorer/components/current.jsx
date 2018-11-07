import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {getSource} from '#/main/app/data'
import {ListSource} from '#/main/app/content/list/containers/source'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

class CurrentDirectory extends Component {
  constructor(props) {
    super(props)

    this.state = {
      source: {}
    }
  }

  componentDidMount() {
    // grab list configuration
    // we rely on the data source for resources
    getSource('resources').then(module => this.setState({
      source: module.default
    }))
  }

  render() {
    return (
      <ListSource
        name={`${this.props.name}.resources`}
        fetch={{
          url: ['apiv2_resource_list', {parent: this.props.currentId}],
          autoload: false
        }}
        source={merge({}, this.state.source, {
          // adds actions to source
          parameters: {
            primaryAction: (resourceNode) => {
              if ('directory' !== resourceNode.meta.type) {
                return this.props.primaryAction && this.props.primaryAction(resourceNode)
              } else {
                // do not open directory, just change the target of the explorer
                return {
                  label: trans('open', {}, 'actions'),
                  type: LINK_BUTTON,
                  target: `/${resourceNode.id}`
                }
              }
            },
            actions: this.props.actions
          }
        })}
        parameters={this.props.listConfiguration}
      />
    )
  }
}

CurrentDirectory.propTypes = {
  name: T.string.isRequired,
  currentId: T.string,
  listConfiguration: T.shape(
    ListParametersTypes.propTypes
  ),
  primaryAction: T.func,
  actions: T.func
}

CurrentDirectory.defaultProps = {
  current: {},
  listConfiguration: {}
}

export {
  CurrentDirectory
}