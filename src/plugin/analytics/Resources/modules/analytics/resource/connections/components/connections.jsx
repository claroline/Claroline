import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {displayDuration} from '#/main/app/intl/date'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'

import {LogConnectCard} from '#/main/core/layout/logs/components/connect-card'

import {selectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const Connections = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('connection_time')}
      actions={[
        {
          name: 'download-connection-times',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-connections', {}, 'actions'),
          file: {
            url: url(['apiv2_log_connect_resource_list_csv', {resource: props.resourceId}])
          },
          group: trans('export')
        }
      ]}
    />

    <ListData
      name={selectors.STORE_NAME + '.connections'}
      fetch={{
        url: ['apiv2_log_connect_resource_list', {resource: props.resourceId}],
        autoload: true
      }}
      definition={[
        {
          name: 'date',
          alias: 'connectionDate',
          type: 'date',
          label: trans('date'),
          displayed: true,
          filterable: false,
          primary: true,
          options: {
            time: true
          }
        }, {
          name: 'user.name',
          alias: 'name',
          type: 'string',
          label: trans('user'),
          displayed: true
        }, {
          name: 'duration',
          type: 'string',
          label: trans('duration'),
          displayed: true,
          filterable: false,
          calculated: (rowData) => rowData.duration !== null ? displayDuration(rowData.duration) : null
        }
      ]}
      card={LogConnectCard}
    />
  </Fragment>

Connections.propTypes = {
  resourceId: T.number.isRequired
}

export {
  Connections
}
