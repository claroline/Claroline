import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {EventList} from '#/plugin/cursus/event/containers/list'

const EventsAll = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('all_events', {}, 'cursus'),
      target: `${props.path}/public`
    }]}
    subtitle={trans('all_events', {}, 'cursus')}
  >
    <EventList
      path={props.path}
      name={props.name}
      url={['apiv2_cursus_event_list', {workspace: props.contextId}]}
      definition={props.definition}
    />
  </ToolPage>

EventsAll.propTypes = {
  name: T.string.isRequired,
  path: T.string.isRequired,
  contextId: T.string,
  definition: T.array
}

export {
  EventsAll
}
