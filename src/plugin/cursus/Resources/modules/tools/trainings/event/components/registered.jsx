import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {EventList} from '#/plugin/cursus/event/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/event/store'
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
        path={props.path}
        name={selectors.STORE_NAME}
        url={['apiv2_cursus_my_events']}
        customDefinition={[
          {
            name: 'session',
            label: trans('session', {}, 'cursus'),
            type: 'training_session',
            displayed: true
          }
        ]}
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
