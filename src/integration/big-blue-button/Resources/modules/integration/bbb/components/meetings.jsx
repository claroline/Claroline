import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {selectors} from '#/integration/big-blue-button/integration/bbb/store'

const BBBMeetings = (props) =>
  <Fragment>
    <ContentTitle level={2} title={trans('meetings', {}, 'bbb')} />

    <ListData
      name={selectors.STORE_NAME+'.meetings'}
      fetch={{
        url: ['apiv2_bbb_integration_meetings'],
        autoload: true
      }}
      definition={[
        {
          name: 'node.name',
          type: 'string',
          label: trans('name'),
          primary: true,
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'node.workspace',
          alias: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayed: true,
          filterable: true,
          sortable: false
        }, {
          name: 'info.participantCount',
          type: 'number',
          label: trans('participants'),
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'info.running',
          type: 'boolean',
          label: trans('opened', {}, 'bbb'),
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'runningOn',
          type: 'choice',
          label: trans('server'),
          options: {
            choices: props.servers.reduce((acc, current) => Object.assign({}, acc, {
              [current.url]: current.url
            }), {})
          }
        }
      ]}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open', {}, 'actions'),
        target: resourceRoute(row.node)
      })}
      actions={(rows) => [
        {
          name: 'close',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-door-closed',
          label: trans('end_meeting', {}, 'bbb'),
          disabled: -1 === rows.findIndex(row => get(row, 'info.running')),
          callback: () => props.endMeetings(rows.map(row => row.id)),
          group: trans('management'),
          dangerous: true
        }
      ]}
    />
  </Fragment>

BBBMeetings.propTypes = {
  servers: T.arrayOf(T.shape({
    url: T.string.isRequired,
    participants: T.number,
    limit: T.number,
    disabled: T.bool
  })),
  endMeetings: T.func.isRequired
}

export {
  BBBMeetings
}