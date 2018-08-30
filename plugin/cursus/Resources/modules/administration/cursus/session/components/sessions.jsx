import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'

import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'

const Sessions = () =>
  <ListData
    name="sessions.list"
    fetch={{
      url: ['apiv2_cursus_session_list'],
      autoload: true
    }}
    primaryAction={SessionList.open}
    delete={{
      url: ['apiv2_cursus_session_delete_bulk']
    }}
    definition={SessionList.definition}
    card={SessionList.card}
  />

export {
  Sessions
}
