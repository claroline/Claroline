import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/events/store'
import {PageListSection} from '#/main/app/page/components/list-section'

const EventsRegistered = (props) =>
  <ToolPage
    breadcrumb={[{
      type: LINK_BUTTON,
      label: trans('my_events', {}, 'cursus'),
      target: `${props.path}/registered`
    }]}
    title={trans('my_events', {}, 'cursus')}
  >
    <PageListSection>
      <EventList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_my_events', {workspace: props.contextId}]}
      />
    </PageListSection>
  </ToolPage>

EventsRegistered.propTypes = {
  path: T.string.isRequired,
  contextId: T.string
}

export {
  EventsRegistered
}
