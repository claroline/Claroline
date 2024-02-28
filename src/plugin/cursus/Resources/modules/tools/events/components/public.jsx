import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {EventList} from '#/plugin/cursus/event/components/list'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {selectors} from '#/plugin/cursus/tools/events/store'

const EventsPublic = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('public_events', {}, 'cursus'),
      target: `${props.path}/public`
    }]}
    subtitle={trans('public_events', {}, 'cursus')}
  >
    <ContentSizing size="full">
      <EventList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_event_public', {workspace: props.contextId}]}
      />
    </ContentSizing>
  </ToolPage>

EventsPublic.propTypes = {
  path: T.string.isRequired,
  contextId: T.string
}

export {
  EventsPublic
}
