import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/core/administration/parameters/main/constants'

const Messages = () =>
  <ListData
    name="messages.list"
    title={trans('connection_messages')}
    fetch={{
      url: ['apiv2_connectionmessage_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/messages/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `messages/form/${rows[0].id}`,
        displayed: !rows[0].locked
      }
    ]}
    delete={{
      url: ['apiv2_connectionmessage_delete_bulk'],
      displayed: (rows) => !rows.find(message => message.locked)
    }}
    definition={[
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'type',
        type: 'string',
        label: trans('type'),
        calculated: (message) => constants.MESSAGE_TYPES[message.type],
        displayed: true,
        filterable: false
      }, {
        name: 'messageType',
        type: 'choice',
        label: trans('type'),
        options: {
          choices: constants.MESSAGE_TYPES
        },
        displayed: false,
        filterable: true
      }, {
        name: 'restrictions.dates[0]',
        alias: 'startDate',
        type: 'date',
        label: trans('start_date'),
        displayed: true
      }, {
        name: 'restrictions.dates[1]',
        alias: 'endDate',
        type: 'date',
        label: trans('end_date'),
        displayed: true
      }, {
        name: 'roles',
        type: 'string',
        label: trans('roles'),
        calculated: (message) => message.roles.map(r => trans(r.translationKey)).join(', '),
        displayed: true,
        filterable: false
      }
    ]}
  />

export {
  Messages
}
