import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {convertTimestampToString} from '#/main/core/administration/logs/connection/utils'
import {LogConnectPlatformCard} from '#/main/core/administration/logs/connection/data/components/log-connect-platform-card'

const ConnectionList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/connections/${row.id}`,
    label: trans('open', {}, 'actions')
  }),
  definition: [
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
      calculated: (rowData) => rowData.duration !== null ? convertTimestampToString(rowData.duration) : null
    }
  ],
  card: LogConnectPlatformCard
}

export {
  ConnectionList
}
