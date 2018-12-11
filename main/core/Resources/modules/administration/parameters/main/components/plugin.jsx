import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const PluginMeta = props =>
  <div className="app-plugin-section app-plugin-meta">
    <p>{trans(`${props.name}_desc`, {}, 'plugin')}</p>

    <h4>Tools</h4>

    <h4>Resources</h4>

    <h4>Administration</h4>
  </div>

PluginMeta.propTypes = {
  name: T.string.isRequired
}

const PluginRequirements = props =>
  <div className="app-plugin-section app-plugin-requirements">
    <h4>Extensions</h4>
    <ul>
      {props.requirements.extensions.map(extension =>
        <li key={extension}>{extension}</li>
      )}
    </ul>

    <h4>Plugins</h4>
    <ul>
      {props.requirements.plugins.map(plugin =>
        <li key={plugin}>{plugin}</li>
      )}
    </ul>

    <h4>Extras</h4>

  </div>

PluginRequirements.propTypes = {
  requirements: T.shape({
    extensions: T.array,
    plugins: T.array,
    extras: T.object
  }).isRequired
}

const PluginParameters = () =>
  <div className="app-plugin-section app-plugin-parameters">
  </div>

PluginParameters.propTypes = {

}

class Plugin extends Component {
  constructor(props) {
    super(props)

    this.state = {
      openedSection: null
    }
  }

  changeSection(section) {
    if (section !== this.state.openedSection) {
      this.setState({openedSection: section})
    } else {
      this.setState({openedSection: null})
    }
  }

  render() {
    return (
      <li className={classes('app-plugin-container', {
        locked: this.props.locked,
        disabled: !this.props.enabled
      })}>
        <div className="app-plugin-header">
          <span className="app-plugin-icon fa fa-cube" />

          <span className="app-plugin-version">{this.props.meta.version}</span>
        </div>

        <div className="app-plugin">
          <h3>
            {trans(`${this.props.name}`, {}, 'plugin')}

            <span className={classes('app-plugin-status fa ', {
              'fa-lock': this.props.locked && this.props.enabled,
              'fa-check': !this.props.locked && this.props.enabled,
              'fa-exclamation-triangle': !this.props.ready && !this.props.enabled
            })} />

            <small>{trans(this.props.meta.origin, {}, 'plugin')}</small>
          </h3>

          <Button
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-info"
            label={trans('information')}
            callback={() => this.changeSection(PluginMeta)}
          />

          <Button
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-server"
            label={trans('requirements')}
            callback={() => this.changeSection(PluginRequirements)}
          />

          {this.props.enabled &&
            <Button
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-cog"
              label={trans('parameters')}
              callback={() => this.changeSection(PluginParameters)}
            />
          }
        </div>

        {this.state.openedSection && React.createElement(this.state.openedSection, this.props)}
      </li>
    )
  }
}

Plugin.propTypes = {
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
}

export {
  Plugin
}
