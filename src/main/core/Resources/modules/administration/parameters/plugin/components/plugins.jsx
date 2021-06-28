import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {Plugin} from '#/main/core/administration/parameters/plugin/components/plugin'

class Plugins extends Component {
  componentDidMount() {
    if (isEmpty(this.props.plugins)) {
      this.props.fetchPlugins()
    }
  }

  render() {
    return (
      <Fragment>
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
      </Fragment>
    )
  }
}

Plugins.propTypes = {
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
    requirements: T.shape({
      extensions: T.array,
      plugins: T.array,
      extras: T.object
    }).isRequired
  })).isRequired,
  fetchPlugins: T.func.isRequired
}

export {
  Plugins
}
