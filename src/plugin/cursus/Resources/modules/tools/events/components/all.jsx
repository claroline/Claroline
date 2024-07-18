import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/events/store'
import {PageListSection} from '#/main/app/page/components/list-section'

const EventsAll = (props) =>
  <ToolPage
    breadcrumb={[{
      type: LINK_BUTTON,
      label: trans('all_events', {}, 'cursus'),
      target: `${props.path}/public`
    }]}
    title={trans('all_events', {}, 'cursus')}
  >
    <PageListSection>
      <EventList
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_event_list', {workspace: props.contextId}]}
      />
    </PageListSection>
  </ToolPage>

EventsAll.propTypes = {
  path: T.string.isRequired,
  contextId: T.string
}

export {
  EventsAll
}
