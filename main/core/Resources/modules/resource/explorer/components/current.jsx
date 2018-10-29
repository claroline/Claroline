import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {getSource} from '#/main/app/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
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

  /**
   * Creates the final list config based on the source definition
   * and the directory configuration.
   *
   * @return {Array}
   */
  computeDefinition() {
    const sourceDefinition = get(this.state, 'source.parameters.definition')
    if (sourceDefinition) {
      // customize source from custom directory config
      if (!isEmpty(this.props.listConfiguration)) {
        return sourceDefinition.map(column => Object.assign({}, column, {
          filterable : -1 !== this.props.listConfiguration.availableFilters.indexOf(column.name),
          sortable   : -1 !== this.props.listConfiguration.availableSort.indexOf(column.name),
          displayable: -1 !== this.props.listConfiguration.availableColumns.indexOf(column.name),
          displayed  : -1 !== this.props.listConfiguration.columns.indexOf(column.name)
        }))
      }

      return sourceDefinition
    }

    return []
  }

  computeCard() {
    const baseCard = get(this.state, 'source.parameters.card')
    if (baseCard) {
      if (get(this.props.listConfiguration, 'card')) {
        // append custom configuration to the card
        const ConfiguredCard = props => React.createElement(baseCard, merge({}, props, {
          display: get(this.props.listConfiguration, 'card')
        }))

        return ConfiguredCard
      } else {
        return baseCard
      }
    }

    // no card defined
    return undefined
  }

  render() {
    return (
      <ListData
        name={`${this.props.name}.resources`}
        fetch={{
          url: ['apiv2_resource_list', {parent: this.props.currentId}],
          autoload: false
        }}
        primaryAction={(resourceNode) => {
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
        }}
        actions={get(this.props.listConfiguration, 'actions') ? this.props.actions : undefined}

        definition={this.computeDefinition()}
        card={this.computeCard()}
        display={{
          current: this.props.listConfiguration.display || listConst.DISPLAY_TILES_SM,
          available: this.props.listConfiguration.availableDisplays
        }}
        count={isEmpty(this.props.listConfiguration) || this.props.listConfiguration.count}
        filterable={isEmpty(this.props.listConfiguration) || !isEmpty(this.props.listConfiguration.availableFilters)}
        sortable={isEmpty(this.props.listConfiguration) || !isEmpty(this.props.listConfiguration.availableSort)}
        paginated={isEmpty(this.props.listConfiguration) || this.props.listConfiguration.paginated}
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