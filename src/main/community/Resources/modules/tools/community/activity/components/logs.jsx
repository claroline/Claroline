import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors} from '#/main/community/tools/community/activity/store/selectors'

const ActivityLogs = (props) =>
  <ListData
    className="component-container"
    name={selectors.STORE_NAME + '.logs'}
    fetch={{
      url: ['apiv2_community_activity_logs', {contextId: props.contextId}],
      autoload: true
    }}
    definition={[
      {
        name: 'action',
        type: 'enum-plus',
        label: trans('action'),
        options: {
          choices: props.actionTypes,
          transDomain: 'log'
        }
      }, {
        name: 'doer',
        type: 'user',
        label: trans('user'),
        displayed: true
      }, {
        name: 'description',
        type: 'html',
        label: trans('description'),
        displayed: true,
        filterable: false,
        sortable: false,
        options: {
          trust: true
        }
      }, {
        name: 'dateLog',
        type: 'date',
        label: trans('date'),
        displayed: true,
        primary: true,
        filterable: false,
        options: {
          time: true
        }
      }
    ]}
    display={{
      available: [listConst.DISPLAY_TABLE, listConst.DISPLAY_TABLE_SM],
      current: listConst.DISPLAY_TABLE
    }}
    selectable={false}
  />

ActivityLogs.propTypes = {
  contextId: T.string.isRequired,
  actionTypes: T.array
}

export {
  ActivityLogs
}
