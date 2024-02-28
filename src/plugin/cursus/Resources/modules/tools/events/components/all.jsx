import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/events/store'

const EventsAll = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('all_events', {}, 'cursus'),
      target: `${props.path}/public`
    }]}
    subtitle={trans('all_events', {}, 'cursus')}
  >
    <ContentSizing size="full">
      <EventList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_event_list', {workspace: props.contextId}]}
      />
    </ContentSizing>
  </ToolPage>

EventsAll.propTypes = {
  path: T.string.isRequired,
  contextId: T.string
}

export {
  EventsAll
}
