import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Heading} from '#/main/core/layout/components/heading'

import {actions, selectors} from '#/main/core/administration/parameters/main/store'
import {Plugin} from '#/main/core/administration/parameters/main/components/plugin'

class PluginsComponent extends Component {
  componentDidMount() {
    if (isEmpty(this.props.plugins)) {
      this.props.fetchPlugins()
    }
  }

  render() {
    return (
      <div>
        <Heading level={2}>
          {trans('plugins')}
          <span className="text-danger">
            <span className="fa fa-fw fa-exclamation-triangle" />
            en construction
          </span>
        </Heading>

        {0 !== this.props.plugins.length &&
          <ul className="app-plugins">
            {this.props.plugins.map(plugin =>
              <Plugin
                key={plugin.id}
                {...plugin}
              />
            )}
          </ul>
        }
      </div>
    )
  }
}

PluginsComponent.propTypes = {
  plugins: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      version: T.string.isRequired,
      origin: T.string.isRequired
    }),
    ready: T.bool.isRequired,
    enabled: T.bool.isRequired,
    locked: T.bool.isRequired,
    hasOptions: T.bool.isRequired,
    requirements: T.shape({
      extensions: T.array,
      plugins: T.array,
      extras: T.object
    }).isRequired
  })).isRequired,
  fetchPlugins: T.func.isRequired
}

const Plugins = connect(
  (state) => ({
    plugins: selectors.plugins(state)
  }),
  (dispatch) => ({
    fetchPlugins() {
      dispatch(actions.fetchPlugins())
    }
  })
)(PluginsComponent)

export {
  Plugins
}
