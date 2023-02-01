import React from 'react'
import {PropTypes as T} from 'prop-types'

import {displayDuration, trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors} from '#/main/community/tools/community/activity/store/selectors'

const ActivityConnections = (props) =>
  <ListData
    className="component-container"
    name={selectors.STORE_NAME + '.connections'}
    fetch={{
      url: props.contextId ?
        ['apiv2_log_connect_workspace_list', {workspace: props.contextId}] :
        ['apiv2_log_connect_platform_list'],
      autoload: true
    }}
    definition={[
      {
        name: 'user',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
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
        name: 'duration',
        type: 'string',
        label: trans('duration'),
        displayed: true,
        filterable: false,
        calculated: (row) => row.duration !== null ? displayDuration(row.duration) : null
      }
    ]}
    display={{
      available: [listConst.DISPLAY_TABLE, listConst.DISPLAY_TABLE_SM],
      current: listConst.DISPLAY_TABLE
    }}
    selectable={false}
  />

ActivityConnections.propTypes = {
  contextId: T.string.isRequired
}

export {
  ActivityConnections
}
