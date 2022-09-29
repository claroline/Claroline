import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, TOGGLE_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentLoader} from '#/main/app/content/components/loader'

const PluginMeta = props =>
  <div className="panel panel-default">
    <ul className="list-group list-group-values">
      <li className="list-group-item">
        {trans('version')}
        <span className="value">
          {props.plugin.meta.version}
        </span>
      </li>

      <li className="list-group-item">
        {trans('author')}
        <span className="value">
          {props.plugin.meta.vendor}
        </span>
      </li>
    </ul>
  </div>

PluginMeta.propTypes = {
  plugin: T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      version: T.string.isRequired,
      vendor: T.string.isRequired,
      bundle: T.string.isRequired
    }),
    ready: T.bool.isRequired,
    enabled: T.bool.isRequired,
    locked: T.bool.isRequired,
    requirements: T.shape({
      /*extensions: T.array,
      plugins: T.array,
      extras: T.object*/
    }).isRequired
  })
}

const Plugin = (props) => {
  if (!props.plugin) {
    return (
      <ContentLoader
        size="lg"
        description={trans('plugin_loading', {}, 'plugin')}
      />
    )
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans(props.plugin.name, {}, 'plugin'),
          target: `${props.path}/plugins/${props.plugin.id}`
        }
      ]}
      subtitle={trans(props.plugin.name, {}, 'plugin')}
      primaryAction="toggle"
      actions={[
        {
          name: 'toggle',
          type: TOGGLE_BUTTON,
          label: trans(props.plugin.enabled ? 'Désactiver le plugin' : 'Activer le plugin'),
          enabled: props.plugin.enabled,
          //disabled: !props.plugin.ready || props.plugin.locked,
          disabled: true,
          primary: true,
          toggle: (enabled) => {
            if (enabled) {
              props.enable(props.plugin)
            } else {
              props.disable(props.plugin)
            }
          }
        }
      ]}
    >
      <div className="row" style={{marginTop: 20}}>
        <div className="col-md-3">
          <PluginMeta plugin={props.plugin} />
        </div>

        <div className="col-md-9">
          <div className="panel panel-default">
            <div className="panel-body">{trans(props.plugin.name+'_desc', {}, 'plugin')}</div>
          </div>
        </div>
      </div>
    </ToolPage>
  )
}

Plugin.propTypes = {
  path: T.string.isRequired,
  plugin: T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      version: T.string.isRequired,
      vendor: T.string.isRequired,
      bundle: T.string.isRequired
    }),
    ready: T.bool.isRequired,
    enabled: T.bool.isRequired,
    locked: T.bool.isRequired,
    requirements: T.shape({
      //extensions: T.array,
      //plugins: T.array,
      //extras: T.object
    }).isRequired
  }),

  enable: T.func.isRequired,
  disable: T.func.isRequired
}

export {
  Plugin
}
