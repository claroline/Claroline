import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'

import {PluginCard} from '#/main/core/administration/plugins/components/card'
import {selectors} from '#/main/core/administration/plugins/store'
import {ToolPage} from '#/main/core/tool/containers/page'

const Plugins = (props) =>
  <ToolPage>
    <ListData
      name={selectors.STORE_NAME+'.plugins'}
      fetch={{
        url: ['apiv2_plugin_list'],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/${row.id}`,
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
