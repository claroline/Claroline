import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'

import {SessionEventList} from '#/plugin/cursus/administration/cursus/session-event/components/session-event-list'

const SessionEvents = () =>
  <ListData
    name="events.list"
    fetch={{
      url: ['apiv2_cursus_session_event_list'],
      autoload: true
    }}
    primaryAction={SessionEventList.open}
    delete={{
      url: ['apiv2_cursus_session_delete_bulk']
    }}
    definition={SessionEventList.definition}
    card={SessionEventList.card}
  />

export {
  SessionEvents
}
