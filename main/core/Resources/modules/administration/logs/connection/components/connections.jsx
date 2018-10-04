import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'

import {ConnectionList} from '#/main/core/administration/logs/connection/components/connection-list'

const Connections = () =>
  <ListData
    name="connections.list"
    fetch={{
      url: ['apiv2_log_connect_platform_list'],
      autoload: true
    }}
    definition={ConnectionList.definition}
    card={ConnectionList.card}
  />

export {
  Connections
}
