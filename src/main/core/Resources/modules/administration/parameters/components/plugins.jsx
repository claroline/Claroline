import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'
import {DataCard} from '#/main/app/data/components/card'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/core/administration/parameters/store'

const PluginCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    className={classes(props.className, {
      'data-card-muted': !get(props.data, 'enabled', false)
    })}
    icon="fa fa-puzzle-piece"
    title={trans(props.data.name, {}, 'plugin')}
    subtitle={get(props.data, 'meta.version')}
    contentText={trans(`${props.data.name}_desc`, {}, 'plugin')}
  />

PluginCard.propTypes = {
  className: T.string,
  data: T.shape({
    id: T.number,
    name: T.string,
    meta: T.shape({
      version: T.string
    })
  }).isRequired
}

const Plugins = (props) =>
  <ToolPage title={trans('plugins')}>
    <ListData
      name={selectors.STORE_NAME+'.plugins'}
      fetch={{
        url: ['apiv2_plugin_list'],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/plugins/${row.id}`,
        label: trans('open', {}, 'actions')
      })}
      definition={[
        {
          name: 'status',
          label: trans('status'),
          type: 'string',
          render: (row) => {
            if (!row.ready) {
              return <span className="fa fa-fw fa-warning" />
            }

            if (row.locked) {
              return <span className="fa fa-fw fa-lock" />
            }
          },
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'name',
          type: 'translation',
          label: trans('name'),
          options: {domain: 'plugin'},
          displayed: true,
          primary: true,
          filterable: false,
          sortable: false
        }, {
          name: 'meta.version',
          alias: 'version',
          type: 'string',
          label: trans('version'),
          displayed: true
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          calculated: (data) => trans(`${data.name}_desc`, {}, 'plugin'),
          displayed: true,
          filterable: false,
          sortable: false
        }
      ]}
      card={PluginCard}
    />
  </ToolPage>

Plugins.propTypes = {
  path: T.string.isRequired,
  enable: T.func.isRequired,
  disable: T.func.isRequired
}

export {
  Plugins
}
